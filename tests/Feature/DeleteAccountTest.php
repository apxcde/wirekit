<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create();

        Volt::actingAs($user);

        Volt::test('profile')
            ->call('deleteAccount')
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
