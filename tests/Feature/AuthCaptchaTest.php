<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthCaptchaTest extends TestCase
{
    public function test_login_page_displays_captcha_challenge(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Captcha');
    }

    public function test_login_requires_valid_captcha(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'user@example.com',
            'password' => 'secret123',
            'captcha_answer' => 'wrong-answer',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['captcha_answer']);
    }
}
