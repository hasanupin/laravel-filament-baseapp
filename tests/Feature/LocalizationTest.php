<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Livewire\Livewire;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_indonesian_is_the_default_and_english_the_fallback(): void
    {
        $this->assertSame('id', config('app.locale'));
        $this->assertSame('en', config('app.fallback_locale'));
    }

    public function test_every_app_string_exists_in_both_languages(): void
    {
        foreach (['app', 'auth', 'passwords'] as $file) {
            $en = array_keys(Arr::dot(require lang_path("en/{$file}.php")));
            $id = array_keys(Arr::dot(require lang_path("id/{$file}.php")));

            $this->assertSame([], array_values(array_diff($en, $id)), "Missing in id/{$file}.php");
            $this->assertSame([], array_values(array_diff($id, $en)), "Missing in en/{$file}.php");
        }
    }

    public function test_no_hardcoded_labels_in_app_code(): void
    {
        $pattern = '/->(label|heading|description|helperText|placeholder|title|emptyStateHeading|emptyStateDescription|modelLabel)\(\s*[\'"]/';
        $offenders = [];

        foreach (Finder::create()->files()->in(app_path())->name('*.php') as $file) {
            if (preg_match($pattern, $file->getContents())) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Use __() for user-facing text');
    }

    public function test_login_page_is_indonesian_by_default(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke akun Anda')
            ->assertSee('English');
    }

    public function test_sidebar_is_indonesian_by_default(): void
    {
        $this->seed([RoleSeeder::class, MenuSeeder::class]);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/')
            ->assertSee('Pengaturan')
            ->assertSee('Peran Pengguna')
            ->assertSee('Menu Pengguna');
    }

    public function test_user_can_switch_to_english_and_back(): void
    {
        $this->seed([RoleSeeder::class, MenuSeeder::class]);
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->from('/')->get('/locale/en')->assertRedirect('/');
        $this->get('/')->assertSee('Settings')->assertSee('User Roles')->assertDontSee('Pengaturan');

        // Switching back only needs the session; a fresh request then renders Indonesian (see sidebar test).
        $this->get('/locale/id')->assertSessionHas('locale', 'id');
    }

    public function test_unknown_locale_is_rejected(): void
    {
        $this->get('/locale/fr')->assertNotFound();
        $this->get('/login')->assertSee('Masuk ke akun Anda');
    }

    public function test_validation_messages_are_indonesian(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $errors = Livewire::test(ManageUsers::class)
            ->callAction(CreateAction::class, data: ['name' => '', 'email' => 'x', 'role_id' => null, 'password' => ''])
            ->errors();

        $this->assertSame('Nama wajib diisi.', $errors->first('mountedActions.0.data.name'));
        $this->assertSame('Email harus berupa alamat email yang valid.', $errors->first('mountedActions.0.data.email'));
    }
}
