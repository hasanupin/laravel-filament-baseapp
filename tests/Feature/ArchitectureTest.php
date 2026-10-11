<?php

namespace Tests\Feature;

use App\Enums\Module;
use Database\Seeders\MenuSeeder;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Enforces the Filament rules in .claude/rules/filament.md: Filament is UI only, Services write data.
 */
class ArchitectureTest extends TestCase
{
    /**
     * @return array<string, string> relative path => contents
     */
    private function phpFiles(string $dir): array
    {
        $files = [];

        foreach (Finder::create()->files()->in($dir)->name('*.php') as $file) {
            $files[$file->getRelativePathname()] = $file->getContents();
        }

        return $files;
    }

    public function test_filament_never_writes_models_directly(): void
    {
        // Writes go through app/Services; Filament only calls them.
        // `$record->update(...)` is a direct write; `app(XService::class)->update(...)` is the allowed path.
        $pattern = '/(\$\w+->(update|delete|save|forceDelete|increment|decrement)\(|::create\(|::query\(\)->(update|delete)|::destroy\(|DB::)/';
        $offenders = [];

        foreach ($this->phpFiles(app_path('Filament')) as $path => $code) {
            if (preg_match($pattern, $code, $match)) {
                $offenders[] = "{$path}: {$match[0]}";
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_toggle_and_inline_edit_columns_go_through_a_service(): void
    {
        $offenders = [];

        foreach ($this->phpFiles(app_path('Filament')) as $path => $code) {
            if (preg_match('/(ToggleColumn|TextInputColumn|SelectColumn|CheckboxColumn)::make/', $code) && ! str_contains($code, 'updateStateUsing')) {
                $offenders[] = $path;
            }
        }

        $this->assertSame([], $offenders, 'Editable columns must use ->updateStateUsing(fn => app(XService::class)->...)');
    }

    public function test_resources_are_wiring_only_with_form_and_table_classes(): void
    {
        $offenders = [];

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $dir = dirname((new \ReflectionClass($resource))->getFileName());
            $code = file_get_contents((new \ReflectionClass($resource))->getFileName());

            foreach (['Schemas' => 'Form.php', 'Tables' => 'Table.php'] as $folder => $suffix) {
                if (! glob("{$dir}/{$folder}/*{$suffix}")) {
                    $offenders[] = class_basename($resource)." is missing {$folder}/*{$suffix}";
                }
            }

            if (preg_match('/(TextInput|Select|Toggle|TextColumn|IconColumn)::make/', $code)) {
                $offenders[] = class_basename($resource).' defines fields/columns inline';
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_resources_live_in_the_admin_panel_folder(): void
    {
        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $this->assertStringStartsWith('App\\Filament\\Admin\\Resources\\', $resource);
        }
    }

    public function test_every_module_is_fully_wired(): void
    {
        $seeded = file_get_contents((new \ReflectionClass(MenuSeeder::class))->getFileName());
        $resources = collect(Filament::getPanel('admin')->getResources());

        foreach (Module::cases() as $module) {
            $resource = $resources->first(fn (string $r) => (new \ReflectionClass($r))->hasProperty('module')
                && (new \ReflectionClass($r))->getStaticPropertyValue('module') === $module);

            $this->assertNotNull($resource, "No resource declares \$module = Module::{$module->name}");
            /** @var class-string<resource> $resource */
            $this->assertNotNull(Gate::getPolicyFor($resource::getModel()), "No policy for {$module->name}");
            $this->assertStringContainsString("Module::{$module->name}->value", $seeded, "MenuSeeder has no icon for {$module->name}");
        }
    }

    public function test_enums_shown_in_the_ui_implement_has_label(): void
    {
        foreach (Finder::create()->files()->in(app_path('Enums'))->name('*.php') as $file) {
            $class = 'App\\Enums\\'.$file->getBasename('.php');

            $this->assertTrue(is_subclass_of($class, HasLabel::class), "{$class} must implement HasLabel");
        }
    }
}
