<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_url_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_the_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }
}
