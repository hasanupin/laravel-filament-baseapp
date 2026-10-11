<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Resources\Users\Pages\ManageUsers;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->superAdmin()->create());
    }

    public function test_lists_users(): void
    {
        $users = User::factory()->count(3)->create();

        Livewire::test(ManageUsers::class)->assertCanSeeTableRecords($users);
    }

    public function test_creates_user_in_modal(): void
    {
        $role = Role::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'role_id' => $role->id,
                'password' => 'secret-123',
                'password_confirmation' => 'secret-123',
            ])
            ->assertHasNoFormErrors();

        $user = User::firstWhere('email', 'jane@example.com');
        $this->assertTrue(Hash::check('secret-123', $user->password));
        $this->assertSame($role->id, $user->role_id);
    }

    public function test_create_validates_input(): void
    {
        $existing = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(CreateAction::class, data: [
                'name' => '',
                'email' => $existing->email,
                'role_id' => null,
                'password' => 'short',
            ])
            ->assertHasFormErrors(['name' => 'required', 'email' => 'unique', 'role_id' => 'required', 'password']);
    }

    public function test_create_requires_matching_password_confirmation(): void
    {
        $role = Role::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'role_id' => $role->id,
                'password' => 'secret-123',
                'password_confirmation' => 'secret-999',
            ])
            ->assertHasFormErrors(['password' => 'confirmed']);

        $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
    }

    public function test_confirmation_is_required_when_password_is_set(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(CreateAction::class, data: [
                'name' => 'Jane',
                'email' => 'jane@example.com',
                'role_id' => Role::factory()->create()->id,
                'password' => 'secret-123',
            ])
            ->assertHasFormErrors(['password_confirmation' => 'required']);
    }

    public function test_edit_with_new_password_needs_matching_confirmation(): void
    {
        $user = User::factory()->superAdmin()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::class)->table($user), data: [
                'password' => 'changed-123',
                'password_confirmation' => 'typo-123',
            ])
            ->assertHasFormErrors(['password' => 'confirmed']);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::class)->table($user), data: [
                'password' => 'changed-123',
                'password_confirmation' => 'changed-123',
            ])
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('changed-123', $user->fresh()->password));
    }

    public function test_edits_user_in_modal_keeping_password_when_blank(): void
    {
        $user = User::factory()->superAdmin()->create();
        $oldHash = $user->password;

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::class)->table($user), data: [
                'name' => 'Renamed',
                'email' => $user->email,
                'password' => '',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame('Renamed', $user->fresh()->name);
        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_deletes_user(): void
    {
        $user = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(DeleteAction::class)->table($user));

        $this->assertModelMissing($user);
    }

    public function test_cannot_delete_self(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(DeleteAction::class)->table(auth()->user()));

        $this->assertModelExists(auth()->user());
    }
}
