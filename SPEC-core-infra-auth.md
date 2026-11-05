# Spec: core-infra-auth

## Objective
Establish the foundational Laravel 11 architecture, migrate the legacy raw PHP codebase safely into `legacy/`, translate the complete database schema into declarative Laravel migrations & seeders, and implement multi-role authentication & Role-Based Access Control (RBAC) supporting distinct access tiers:
- **Admin**: Full system access (ERP + Settings + Users)
- **Staff_Warehouse**: Warehouse & inventory management, restock orders, serial numbers
- **Staff_Tech**: After-sales support tickets, RMA, technician scheduling
- **Customer**: Storefront ordering, saved custom builds, warranty lookup, support tickets

## Tech Stack
- **Framework**: Laravel 11.x
- **Language**: PHP 8.2+
- **Database**: MySQL (compatible with MariaDB / XAMPP)
- **Frontend Assets**: Standard Blade views with Plain CSS and Plain JavaScript (no npm build step required)
- **Authentication**: Session-based auth with custom role-guard middleware

## Commands
- **Install / Setup**: `composer create-project laravel/laravel . --prefer-dist` (or via composer in workspace)
- **Serve Application**: `php artisan serve --port=8000`
- **Run Migrations**: `php artisan migrate:fresh --seed`
- **Run Tests**: `php artisan test`
- **Code Lint / Style**: `vendor/bin/pint` (or `php -l`)

## Project Structure
```
legacy/                     → Backup of existing raw PHP files (app, controllers, models, views, etc.)
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegisterController.php
│   │   └── DashboardController.php
│   └── Middleware/
│       └── RoleMiddleware.php
├── Models/
│   ├── User.php
│   ├── Role.php
│   └── ... (Domain models created alongside migrations)
database/
├── migrations/             → Full relational schema (Roles, Users, Products, Orders, etc.)
└── seeders/
    ├── DatabaseSeeder.php
    └── RoleAndUserSeeder.php
resources/
├── css/
│   └── app.css             → Plain CSS stylesheets
├── js/
│   └── app.js              → Plain JavaScript helpers
└── views/
    ├── layouts/
    │   ├── app.blade.php   → Customer / Public base layout
    │   └── erp.blade.php   → ERP Admin & Staff layout
    └── auth/
        ├── login.blade.php
        └── register.blade.php
tests/
└── Feature/
    ├── AuthTest.php
    └── RoleAccessTest.php
```

## Code Style
Strict adherence to PSR-12 and modern Laravel 11 conventions. Use strongly typed properties, Form Requests for validation, and standard Blade components without JavaScript framework overhead.

```php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Redirect based on role
            return Auth::user()->role->RoleName === 'Customer'
                ? redirect()->intended(route('home'))
                : redirect()->intended(route('erp.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }
}
```

## Testing Strategy
- **Framework**: PHPUnit / Pest (Laravel standard)
- **Feature Tests**:
  - `AuthTest`: Tests registration, login, logout, remember me, and invalid credentials.
  - `RoleAccessTest`: Tests that `Customer` cannot access `/erp/*`, `Staff_Warehouse` can access inventory routes but not user administration, and `Admin` has unrestricted access.
- **Database Testing**: In-memory SQLite or MySQL test database running fresh migrations.

## Boundaries
- **Always**:
  - Keep all original raw PHP code safe under `legacy/` before modifying root structure.
  - Use Laravel Eloquent migrations and foreign key constraints matching `database.sql`.
  - Hash all passwords using `Hash::make()` (Bcrypt / Argon2id).
  - Use Plain CSS & Vanilla JS as requested (no Tailwind build / Node compilation dependencies).
- **Ask First**:
  - Altering relational foreign key constraints or column data types from `database.sql`.
  - Adding third-party composer packages beyond core Laravel.
- **Never**:
  - Store plaintext passwords or bypass CSRF protection on forms.
  - Hardcode role IDs directly in controller logic (use role names / constants).
  - Delete or overwrite user data in `legacy/`.

## Success Criteria
1. Legacy raw PHP files safely archived in `legacy/` without loss.
2. Fresh Laravel 11 instance boots successfully with `php artisan --version`.
3. `php artisan migrate:fresh --seed` successfully executes all migrations based on `database.sql` and populates seed accounts.
4. User login & registration functional with role separation:
   - Logging in as `Admin`, `Staff_Warehouse`, or `Staff_Tech` redirects to the ERP Dashboard.
   - Logging in as `Customer` redirects to the storefront.
5. Role-based route middleware blocks unauthorized users (e.g. 403 Forbidden for customer trying to access ERP endpoints).
6. Automated tests pass (`php artisan test`).

## Open Questions & Decisions
1. **Default Seed Users**: Confirmed. Standard development credentials (`admin@custompc.local`, `warehouse@custompc.local`, `tech@custompc.local`, `customer@custompc.local` with password `password123`).
2. **ERP Layout Theme**: Confirmed. Modern dark sidebar dashboard theme with high-contrast text and sleek component cards.
