---
paths:
  - "app/**/*.php"
  - "tests/**/*.php"
  - "database/seeders/**/*.php"
  - "lang/**"
---

# Filament architecture rules

Filament is the UI layer only. Data is written only by `app/Services`, so the same logic serves Filament, APIs, jobs, commands and tests.
`tests/Feature/ArchitectureTest.php` enforces the rules marked **[test]**. Run it after any Filament change.

## Layers

| Layer | Lives in | Holds |
|---|---|---|
| UI | `app/Filament/<Panel>/` (`Admin/` today) | Resources, `Schemas/*Form`, `Tables/*Table`, Pages, Widgets |
| Shared UI | `app/Filament/Shared/` | Reusable fields (`Forms/`), columns (`Tables/`), actions (`Actions/`), traits (`Concerns/`) |
| Validation | `app/Http/Requests/<Model>/` | `static rulesFor(?Model $record): array`; the same rules serve HTTP and Filament |
| Logic | `app/Services/<Model>Service.php` | `create` / `update` / `delete` and every other operation that writes |
| Access | `app/Policies/<Model>Policy.php` | `$user->hasPermission(Module::X, PermissionAction::Y)` per ability |
| Values | `app/Enums/` | Backed enums implementing `HasLabel` (plus `HasColor` / `HasIcon` for statuses) |

## Rules

1. **Resources are wiring only. [test]** A resource holds the model, `$module`, the model label, `form()` → `XForm::configure()`, `table()` → `XsTable::configure()`, and pages. It defines no fields or columns inline.
2. **Nothing in `app/Filament` writes models directly. [test]**
   - No `$record->update()`, `::create()`, `->delete()` or `DB::` calls.
   - `CreateAction` / `EditAction` use `->using(fn (...) => app(XService::class)->create|update(...))`.
   - Deletes use `App\Filament\Shared\Actions\ServiceDeleteAction::make(fn ($r) => app(XService::class)->delete($r))`.
   - Custom actions use `->action(fn ($r) => app(XService::class)->operation($r))->authorize('update')`.
   - Page hooks (`handleRecordUpdate`, `reorderTable`, …) call a service.
3. **Editable columns go through a service. [test]** `ToggleColumn`, `TextInputColumn`, `SelectColumn` and `CheckboxColumn` need `->updateStateUsing(fn ($record, $state) => app(XService::class)->update($record, [...]))`.
4. **Services own the business rules.** They signal domain errors with `ValidationException::withMessages([...])`, translated. They wrap multi-step writes in `DB::transaction` and fire events from there. They never read `request()`. The acting user is `auth()->user()` or a parameter.
5. **Validation lives in Request classes.** Each field pulls its own rule, e.g. `->rules(fn (?User $record) => UpsertUserRequest::rulesFor($record)['email'])`. Filament validates in the modal, and the service trusts its input.
6. **Authorization is a Policy per model.** Filament applies `viewAny` / `create` / `update` / `delete` (and `reorder`) automatically. Don't add `->visible(auth()->user()->...)` checks for these.
7. **Enums shown in the UI implement `HasLabel`. [test]**
   - Status enums also implement `HasColor` (and `HasIcon` if useful), with the model casting the column to the enum.
   - Then `Select::make('status')->options(Status::class)` and `TextColumn::make('status')->badge()` need no extra code.
   - Labels come from `__()`.
8. **Eager-load what the table shows**: `->modifyQueryUsing(fn ($q) => $q->with([...]))`, and `->counts('rel')` for counts.
9. **Extract a field, column or action into `app/Filament/Shared` on its second use**, not before. For example, `HeroiconSelect` and `ServiceDeleteAction`.
10. **One panel per kind of user** (`Filament/Admin/`, later e.g. `Filament/Member/` with its own `MemberPanelProvider`). Don't branch on user type inside one panel.
11. **Create and edit happen in modals**: `make:filament-resource X --simple --not-embedded` gives a single `ManageXs` page plus split `Schemas/` and `Tables/` files.
12. **Every module is fully wired. [test]** That means a `Module` enum case, a resource with `$module` and `HasManagedNavigation`, a policy, and an icon in `MenuSeeder`. The `/filament-module` skill does all of this.
13. **Every label goes through `__()`** with keys in both `lang/id/app.php` and `lang/en/app.php` (`LocalizationTest` checks this).

## Tests (PHPUnit)

- Service tests in `tests/Feature/Services/` exercise the rules directly, without UI.
- Filament tests in `tests/Feature/Admin/` use Livewire:
  ```php
  Livewire::test(ManageUsers::class)
      ->callAction(TestAction::make(EditAction::class)->table($user), data: [...])
      ->assertHasNoFormErrors();
  ```
- Build users with factories: `User::factory()->superAdmin()`, `->withRole(Role::factory()->withPermissions([...]))`.
