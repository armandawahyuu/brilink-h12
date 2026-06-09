<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_update_password(): void
    {
        $response = $this->put(route('settings.update.password'));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_update_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('settings.update.password'), [
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertRedirect(route('settings.index'));
        $this->assertTrue(Hash::check('NewPassword456', $user->fresh()->password));
    }

    public function test_current_password_must_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        $response = $this->actingAs($user)->from(route('settings.index'))->put(route('settings.update.password'), [
            'current_password' => 'WrongPassword123',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }
}
