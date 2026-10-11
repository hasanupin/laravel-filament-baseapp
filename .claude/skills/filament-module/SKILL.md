---
name: filament-module
description: Add a new admin module (model + Filament resource with modal CRUD) to this base app following the project's Filament architecture rules - service layer, request rules, policy, role permissions, managed menu, translations and tests. Use when the user asks to add/create a module, menu, resource, CRUD page or "master data" screen.
---

# Add a Filament module

Follow `.claude/rules/filament.md`. The **Users** module is the reference implementation; copy its shape:
- `app/Filament/Admin/Resources/Users/` (resource, `Pages/`, `Schemas/`, `Tables/`)
- `app/Services/UserService.php`
- `app/Http/Requests/User/UpsertUserRequest.php`
- `app/Policies/UserPolicy.php`
- `tests/Feature/Admin/UserResourceTest.php` and `tests/Feature/Services/UserServiceTest.php`

Below, `Product` / `products` / `Products` stand for the singular model, the module key and the plural. Run commands with `docker compose exec app …`.

## 0. Ask only if unclear
Pin down three things before starting:
- the fields and their rules
- which CRUD actions the module needs; read-only or edit-only modules override `Module::actions()`
- any status-like field, which becomes an enum

## 1. Tests first (RED)
- `tests/Feature/Services/ProductServiceTest.php`: create, update, delete, plus every business rule (each one throws `ValidationException`).
- `tests/Feature/Admin/ProductResourceTest.php`:
  - lists records
  - creates and edits in the modal (`callAction(CreateAction::class, data: [...])`, `TestAction::make(EditAction::class)->table($record)`)
  - validation errors
  - delete
  - a role with only `products: ['view']` sees no create, edit or delete buttons
- Run them and watch them fail.

## 2. Model + migration + factory
```bash
php artisan make:model Product -mf
```
- Use `#[Fillable([...])]` like `app/Models/User.php`.
- Cast status columns to their enum.

## 3. Enum (if there's a status)
Put it in `app/Enums/ProductStatus.php`, implementing `HasLabel` and `HasColor`. Labels come from `__('app.products.status.<case>')`.

## 4. Register the module
- `app/Enums/Module.php`: add `case Products = 'products';`. If not every CRUD action applies, add it to `actions()`.
- Add translations to **both** `lang/id/app.php` and `lang/en/app.php`:
  - `modules.products`
  - `products.model`
  - `products.fields.*`
  - any service error messages

## 5. Validation
Create `app/Http/Requests/Product/UpsertProductRequest.php` with `rules()` returning `static::rulesFor($this->route('product'))`, and `public static function rulesFor(?Product $product = null): array`. Unique rules use `->ignore($product)`.

## 6. Service
Create `app/Services/ProductService.php` with `create(array $data)`, `update(Product $p, array $data)` and `delete(Product $p)`.
- Business rules throw `ValidationException::withMessages(['product' => __('app.products.<rule>')])`.
- Wrap multi-step writes in `DB::transaction`.

## 7. Policy
Create `app/Policies/ProductPolicy.php`: copy `UserPolicy` and change it to `Module::Products`. Remove the methods for actions the module doesn't offer, or have them return `false`.

## 8. Filament resource (modal CRUD, split files)
```bash
php artisan make:filament-resource Product --simple --not-embedded
```
Then shape it like Users:
- **`ProductResource`:**
  - add `use HasManagedNavigation;` and `protected static Module $module = Module::Products;`
  - add `getModelLabel()` returning `__('app.products.model')`
  - `form()` and `table()` only delegate to the generated classes
- **`Schemas/ProductForm::configure()`:** every field gets `->label(__(...))` and `->rules(fn (?Product $record) => UpsertProductRequest::rulesFor($record)['field'])`.
- **`Tables/ProductsTable::configure()`:**
  - `->modifyQueryUsing(fn ($q) => $q->with([...]))`
  - labelled columns
  - `EditAction::make()->using(fn (Product $record, array $data) => app(ProductService::class)->update($record, $data))`
  - `ServiceDeleteAction::make(fn (Product $record) => app(ProductService::class)->delete($record))`
  - no bulk actions unless the service has a bulk method
- **`Pages/ManageProducts`:** `CreateAction::make()->using(fn (array $data) => app(ProductService::class)->create($data))`
- Reuse `app/Filament/Shared/*`. If you write a field or column that already exists elsewhere, extract it there.

## 9. Menu + permissions
- In `database/seeders/MenuSeeder.php`, add `Module::Products->value => Heroicon::Outlined…` to `$icons`. Then run `php artisan db:seed --class=MenuSeeder` (idempotent).
- In `RoleSeeder`, give `Admin` the actions it should have on `products`.
- The role checkbox grid and the sidebar pick the module up automatically.

## 10. Verify (GREEN)
```bash
docker compose exec app vendor/bin/pint --dirty
docker compose exec app php artisan test
```
- `ArchitectureTest` (wiring, no direct writes, enums) and `LocalizationTest` (labels, both languages) must pass.
- Check the screen as `superadmin@example.com` and as a limited role.
