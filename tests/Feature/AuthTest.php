<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        foreach (["/", "/clients", "/services", "/tariff-lists", "/service-orders", "/invoices"] as $uri) {
            $this->get($uri)->assertRedirect(route("login"));
        }
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(["password" => "secret-pass"]);

        $this->post(route("login.store"), ["email" => $user->email, "password" => "secret-pass"])
            ->assertRedirect(route("dashboard"));
        $this->assertAuthenticatedAs($user);

        $this->post(route("logout"))->assertRedirect(route("login"));
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->from(route("login"))
            ->post(route("login.store"), ["email" => $user->email, "password" => "wrong"])
            ->assertSessionHasErrors("email");
        $this->assertGuest();
    }
}
