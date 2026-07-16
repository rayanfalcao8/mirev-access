<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_is_available(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_admin_resources_require_authentication(): void
    {
        $this->get('/admin/sites')->assertRedirect('/admin/login');
        $this->get('/admin/plans')->assertRedirect('/admin/login');
        $this->get('/admin/customers')->assertRedirect('/admin/login');
        $this->get('/admin/subscriptions')->assertRedirect('/admin/login');
        $this->get('/admin/orders')->assertRedirect('/admin/login');
        $this->get('/admin/incidents')->assertRedirect('/admin/login');
    }
}
