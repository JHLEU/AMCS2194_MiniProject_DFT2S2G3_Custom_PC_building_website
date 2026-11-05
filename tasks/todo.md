# Tasks: core-infra-auth

- [x] Task 1: Archive legacy raw PHP files into `legacy/`
  - Acceptance: Existing `app/`, `config/`, `controllers/`, `models/`, `views/`, `public/`, `index.php`, and `run_server.bat` are safely moved into `legacy/`, leaving the root directory clean for Laravel installation.
  - Verify: Directory check shows `legacy/` contains original files and root no longer has legacy raw PHP files.
  - Files: `legacy/*`

- [x] Task 2: Scaffold Laravel 11 application
  - Acceptance: Laravel 11 files (artisan, composer.json, bootstrap, config, routes, etc.) initialized at root, `.env` configured with `APP_NAME="Custom PC & ERP"`, and SQLite configured for test environment.
  - Verify: Run `php artisan --version` succeeds.
  - Files: `composer.json`, `.env`, `artisan`, `bootstrap/app.php`

- [x] Task 3: Create complete database migrations based on `database.sql`
  - Acceptance: Migrations created for `roles`, `users`, `categories`, `suppliers`, `products`, `supplier_hardware_mapping`, `restock_orders`, `restock_order_items`, `serial_numbers`, `orders`, `order_items`, `payments`, `warranties`, `support_tickets`, `tech_schedules`, `saved_builds`, and `chat_logs`.
  - Verify: `php artisan migrate` executes with 0 errors.
  - Files: `database/migrations/*`

- [x] Task 4: Implement Eloquent Models and Seeders
  - Acceptance: Models `Role` and `User` (and base models) defined with relationships and role constants. `DatabaseSeeder` & `RoleAndUserSeeder` seed the 4 roles (`Admin`, `Staff_Warehouse`, `Staff_Tech`, `Customer`) and initial development accounts with password `password123`.
  - Verify: `php artisan migrate:fresh --seed` runs cleanly and populates database.
  - Files: `app/Models/Role.php`, `app/Models/User.php`, `database/seeders/DatabaseSeeder.php`, `database/seeders/RoleAndUserSeeder.php`

- [x] Task 5: Build Multi-Role Auth, RBAC Middleware, and Controllers
  - Acceptance: `RoleMiddleware` restricts routes based on role name/permission. `AuthController` handles login, register, and logout with role-based redirection (`Admin`/`Staff_*` → `/erp/dashboard`, `Customer` → `/`).
  - Verify: Role middleware correctly allows authorized roles and returns 403 on unauthorized access.
  - Files: `app/Http/Middleware/RoleMiddleware.php`, `app/Http/Controllers/AuthController.php`, `app/Http/Controllers/Erp/DashboardController.php`, `routes/web.php`

- [x] Task 6: Implement Blade Layouts, Auth Views, and Dark Theme ERP Dashboard
  - Acceptance: Public layout (`layouts/app.blade.php`), dark sidebar ERP layout (`layouts/erp.blade.php`), login (`auth/login.blade.php`), register (`auth/register.blade.php`), and ERP dashboard view created using Plain CSS & Plain JS.
  - Verify: Pages render cleanly with dark theme on `/erp/dashboard` and proper navigation.
  - Files: `resources/views/layouts/app.blade.php`, `resources/views/layouts/erp.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, `resources/views/erp/dashboard.blade.php`, `public/css/style.css`

- [x] Task 7: Automated Feature Tests
  - Acceptance: Feature tests verifying registration, login authentication, role redirection, and route protection gates.
  - Verify: `php artisan test` passes with green results.
  - Files: `tests/Feature/AuthTest.php`, `tests/Feature/RoleAccessTest.php`
