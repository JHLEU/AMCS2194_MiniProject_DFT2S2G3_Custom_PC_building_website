# Implementation Plan: core-infra-auth

## 1. Overview
This plan establishes the foundation for the Custom PC Building & ERP system by safely archiving legacy raw PHP code, bootstrapping Laravel 11, implementing all database migrations matching `database.sql`, and setting up multi-role authentication with a modern dark-themed ERP dashboard layout.

## 2. Major Components & Dependencies
1. **Legacy Archiving**: Move all existing raw PHP files into `legacy/`. (No dependencies)
2. **Laravel 11 Setup**: Initialize Laravel 11 app skeleton and `.env` configuration. (Depends on 1)
3. **Database Schema & Models**: Generate migrations matching all tables in `database.sql` and Eloquent models (`Role`, `User`, etc.). (Depends on 2)
4. **Seeders**: Seed 4 distinct roles (`Admin`, `Staff_Warehouse`, `Staff_Tech`, `Customer`) and initial test accounts. (Depends on 3)
5. **Auth & RBAC**: Auth controllers, session management, `RoleMiddleware`, and role-based redirect logic. (Depends on 4)
6. **Views & Assets**: Plain CSS styling, public layout, dark-theme ERP sidebar layout, login & register views. (Depends on 5)
7. **Automated Tests**: Unit & feature tests for authentication and role access boundaries. (Depends on 5, 6)

## 3. Implementation Order
1. Archiving (`legacy/`)
2. Laravel 11 Scaffold & Environment Setup
3. Migrations & Seeders
4. Models & RBAC Middleware
5. Auth Controllers & Blade Views (Dark theme ERP layout)
6. Feature Verification & Automated Tests

## 4. Risks & Mitigations
- **Risk**: File collision during Laravel initialization in an existing git/workspace directory.
  - **Mitigation**: Move all raw PHP directories (`app`, `config`, `controllers`, `models`, `views`, `public`, etc.) into `legacy/` before bootstrapping Laravel.
- **Risk**: MySQL connection or port variance on local developer machines.
  - **Mitigation**: Use `.env` with standard `127.0.0.1:3306`, and configure `phpunit.xml` with in-memory SQLite (`:memory:`) so automated tests pass anywhere without external database dependencies.
- **Risk**: Role naming mismatch between frontend and backend.
  - **Mitigation**: Define explicit constants on `App\Models\Role` (`ROLE_ADMIN`, `ROLE_WAREHOUSE`, `ROLE_TECH`, `ROLE_CUSTOMER`).

## 5. Verification Checkpoints
- [x] Checkpoint 1: Legacy files safely moved to `legacy/`.
- [x] Checkpoint 2: `php artisan --version` runs cleanly and reports Laravel 11+.
- [x] Checkpoint 3: `php artisan migrate:fresh --seed` creates all tables and seeds the 4 roles and seed users.
- [x] Checkpoint 4: Web server serves `/login`, `/register`, `/erp/dashboard`, with dark sidebar on ERP.
- [x] Checkpoint 5: `php artisan test` passes 100%.
