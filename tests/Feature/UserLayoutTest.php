<?php

namespace Tests\Feature;

use Tests\TestCase;

/** The signed-in area uses the sidebar shell; the public site keeps its top navbar. PJAX only swaps within one layout key. */
class UserLayoutTest extends TestCase
{
    public function test_signed_in_views_extend_the_sidebar_layout(): void
    {
        $files = array_merge(
            glob(resource_path('views/modules/user/*.blade.php')),
            glob(resource_path('views/modules/note/*.blade.php')),
        );

        foreach ($files as $file) {
            $this->assertStringContainsString("@extends('layouts.user')", file_get_contents($file), basename($file));
        }

        $this->assertStringContainsString("'layouts.user'", file_get_contents(app_path('Modules/User/Controllers/AccountController.php')));
    }

    public function test_layout_keys_differ_so_pjax_falls_back_to_a_full_load_between_shells(): void
    {
        $user = file_get_contents(resource_path('views/layouts/user.blade.php'));
        $main = file_get_contents(resource_path('views/layouts/main.blade.php'));

        $this->assertStringContainsString('data-layout="user"', $user);
        $this->assertStringContainsString("\$_GET['layout'] == 'user'", $user);
        $this->assertStringContainsString('data-layout="main"', $main);
        $this->assertStringContainsString("\$_GET['layout'] == 'main'", $main);
    }

    public function test_the_shell_is_state_driven_and_remembered_in_a_cookie(): void
    {
        $user = file_get_contents(resource_path('views/layouts/user.blade.php'));

        $this->assertStringContainsString("request()->cookie('sidebar_state'", $user);
        $this->assertStringContainsString('data-app-sidebar', $user);
        $this->assertStringContainsString("'sidebar_state'", file_get_contents(base_path('bootstrap/app.php')));
    }

    public function test_signed_out_visitors_still_go_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login?redirect=%2Fdashboard');
        $this->get('/notes')->assertRedirect('/login?redirect=%2Fnotes');
        $this->get('/account/update')->assertRedirect('/login?redirect=%2Faccount%2Fupdate');
    }
}
