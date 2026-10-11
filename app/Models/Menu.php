<?php

namespace App\Models;

use App\Enums\Module;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

#[Fillable(['key', 'label', 'icon', 'group', 'sort', 'is_active'])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * One query per request for the whole sidebar.
     */
    public static function forModule(Module $module): ?self
    {
        return once(fn () => static::all()->keyBy('key'))->get($module->value);
    }

    public function module(): ?Module
    {
        return Module::tryFrom($this->key);
    }

    /**
     * Admin override, else the module's name in the current language.
     */
    public function displayLabel(): string
    {
        return $this->label ?: ($this->module()?->label() ?? $this->key);
    }

    /**
     * Known group keys (e.g. "settings") are translated; anything an admin typed is shown as-is.
     */
    public function displayGroup(): ?string
    {
        if (blank($this->group)) {
            return null;
        }

        return Lang::has("app.menu_groups.{$this->group}") ? __("app.menu_groups.{$this->group}") : $this->group;
    }
}
