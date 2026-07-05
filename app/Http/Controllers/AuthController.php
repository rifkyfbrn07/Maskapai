<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailOtp;

class AuthController extends Controller
{
    /**
     * Determine whether Google reCAPTCHA is enabled.
     */
    private function captchaEnabled(): bool
    {
        return filter_var(env('RECAPTCHA_ENABLED', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Create a simple local captcha challenge and store it in session.
     */
    private function prepareCaptcha(): void
    {
        $first = random_int(2, 9);
        $second = random_int(1, 9);
        $operator = random_int(0, 1) === 0 ? '+' : '-';
        $answer = $operator === '+' ? $first + $second : $first - $second;

        session()->put('captcha_question', "$first $operator $second");
        session()->put('captcha_answer', (string) $answer);
    }

    /**
     * Verify the captcha challenge for the current request.
     */
    private function verifyCaptcha(Request $request): bool
    {
        if ($this->captchaEnabled()) {
            $recaptchaToken = $request->input('g-recaptcha-response');
            if (!$recaptchaToken) {
                return false;
            }

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $recaptchaToken,
                'remoteip' => $request->ip(),
            ]);

            return (bool) $response->json('success');
        }

        $provided = trim((string) $request->input('captcha_answer'));
        $expected = trim((string) session('captcha_answer'));

        return $provided !== '' && $expected !== '' && strtolower($provided) === strtolower($expected);
    }

    /**
     * Show registration form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect($this->getRedirectPath(Auth::user()));
        }
        return view('auth.register');
    }

    /**
     * Process registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Google reCAPTCHA Verification
        if (env('RECAPTCHA_ENABLED', false)) {
            $recaptchaToken = $request->input('g-recaptcha-response');
            if (!$recaptchaToken) {
                return back()->withErrors(['g-recaptcha' => 'Please check the reCAPTCHA box.'])->withInput();
            }

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $recaptchaToken,
                'remoteip' => $request->ip(),
            ]);

            if (!$response->json('success')) {
                return back()->withErrors(['g-recaptcha' => 'reCAPTCHA verification failed. Please try again.'])->withInput();
            }
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $verificationRequired = env('EMAIL_VERIFICATION_REQUIRED', true);

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer', // Always customer by default on registration
            'verification_code' => $otp,
            'email_verified_at' => $verificationRequired ? null : now(),
        ]);

        if ($verificationRequired) {
            // Send Email Verification
            try {
                Mail::to($user->email)->send(new MailOtp($otp));
            } catch (\Exception $e) {
                // Log error or output it in dev. We still proceed so they can verify via logs
                logger()->error('SMTP failed: ' . $e->getMessage());
            }
        }

        // Log the user in
        Auth::login($user);

        return redirect()->intended($this->getRedirectPath($user));
    }

    /**
     * Show login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->getRedirectPath(Auth::user()));
        }

        $this->prepareCaptcha();

        return view('auth.login', ['captchaQuestion' => session('captcha_question')]);
    }

    /**
     * Process login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!$this->verifyCaptcha($request)) {
            $this->prepareCaptcha();

            if ($this->captchaEnabled()) {
                return back()->withErrors(['g-recaptcha' => 'Please complete the captcha verification.'])->withInput();
            }

            return back()->withErrors(['captcha_answer' => 'Captcha answer is incorrect.'])->withInput();
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            return redirect()->intended($this->getRedirectPath($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show verification notice/form.
     */
    public function showVerify()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ($user->email_verified_at) {
            return redirect('/');
        }

        return view('auth.verify', ['email' => $user->email]);
    }

    /**
     * Process verification.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            return redirect()->route('login');
        }

        if ($user->verification_code === $request->otp) {
            $user->email_verified_at = now();
            $user->verification_code = null;
            $user->save();

            return redirect()->route('home')->with('success', 'Your account has been verified successfully!');
        }

        return back()->withErrors(['otp' => 'The verification code (OTP) you entered is incorrect.']);
    }

    /**
     * Resend verification OTP.
     */
    public function resendVerify()
    {
        $user = Auth::user();
        if (!$user || !($user instanceof User)) {
            return redirect()->route('login');
        }

        if ($user->email_verified_at) {
            return redirect('/');
        }

        $otp = rand(100000, 999999);
        $user->verification_code = $otp;
        $user->save();

        try {
            Mail::to($user->email)->send(new MailOtp($otp));
        } catch (\Exception $e) {
            logger()->error('SMTP failed: ' . $e->getMessage());
        }

        return back()->with('success', 'A new verification code (OTP) has been sent to your email.');
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Helper to get path depending on role.
     */
    private function getRedirectPath($user)
    {
        if (env('EMAIL_VERIFICATION_REQUIRED', true) && !$user->email_verified_at && $user->role === 'customer') {
            return route('verification.notice');
        }

        switch ($user->role) {
            case 'admin':
                return route('admin.dashboard');
            case 'staff':
                return route('staff.dashboard');
            case 'manager':
                return route('manager.dashboard');
            default:
                return route('home');
        }
    }
}
