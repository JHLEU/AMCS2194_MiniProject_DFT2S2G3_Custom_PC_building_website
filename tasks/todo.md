# Tasks: frontend-storefront

## Task 1: Storefront Controllers & Routing
**Description:**
- Implement `HomeController`: loads featured pre-built PCs, category summaries, and reviews.
- Implement `PcBuilderController`: renders interactive configurator (`index`) and packages custom build into checkout session (`buildToCart`).
- Implement `CalculatorController`: renders bottleneck calculator view (`index`) and handles AJAX calculations (`calculate`).
- Implement `ContactController`: renders store location, WhatsApp hotline, and workshop hours (`index`).
- Register storefront routes in `routes/web.php` and update header navigation in `layouts/app.blade.php`.

**Acceptance criteria:**
- [x] `GET /` routed to `HomeController@index`.
- [x] `GET /builder` and `POST /builder/checkout` routed to `PcBuilderController`.
- [x] `GET /calculator` and `POST /calculator/evaluate` routed to `CalculatorController`.
- [x] `GET /contact` routed to `ContactController@index`.
- [x] Header links point to active routes.

**Verification:**
- [x] `php artisan route:list` shows all storefront routes.

**Dependencies:** None

**Files likely touched:**
- `app/Http/Controllers/HomeController.php`
- `app/Http/Controllers/PcBuilderController.php`
- `app/Http/Controllers/CalculatorController.php`
- `app/Http/Controllers/ContactController.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`

**Estimated scope:** Medium (6 files)

---

## Task 2: Homepage & Contact Views (`home.blade.php`, `contact.blade.php`)
**Description:**
- Update `resources/views/home.blade.php`:
  - Hero banner with CTA to PC Builder.
  - Featured Gaming & Workstation pre-built rigs.
  - Hardware categories grid with live product count badges.
  - Interactive Google customer reviews carousel (Plain JS / jQuery).
  - Value proposition & store trust badges (2-year warranty, genuine parts, stress testing).
- Create `resources/views/contact.blade.php`:
  - WhatsApp direct chat link, hotline, workshop address, and business hours.
- Add modern styling classes to `public/css/style.css`.

**Acceptance criteria:**
- [x] Homepage renders hero, featured builds, categories, and reviews carousel.
- [x] Reviews carousel rotates smoothly with next/prev buttons.
- [x] Contact page displays store location and clickable WhatsApp link.

**Verification:**
- [x] Browser rendering and feature test assertions.

**Dependencies:** Task 1

**Files likely touched:**
- `resources/views/home.blade.php`
- `resources/views/contact.blade.php`
- `public/css/style.css`

**Estimated scope:** Medium (3 files)

---

## Task 3: Interactive PC Builder UI & Dynamic Compatibility Integration
**Description:**
- Implement `resources/views/builder/index.blade.php`:
  - 8 component selection slots (CPU, Motherboard, RAM, GPU, Storage, PSU, Cooler, Case).
  - Component selection modals or dropdowns with filtered compatible parts.
  - Sticky build summary panel: live subtotal, dynamic compatibility badge, live wattage headroom bar, and bottleneck synergy preview.
  - "Proceed to Checkout" button that posts selected items to `/builder/checkout` and redirects to `/checkout`.
- Add client-side builder script in `public/js/builder.js`:
  - Handles item selection/removal.
  - Debounced AJAX calls to `/api/compatibility/evaluate` to update compatibility, wattage headroom, and warnings in real-time.
  - Updates running price tally.

**Acceptance criteria:**
- [x] Builder renders all 8 hardware slots.
- [x] Selecting an incompatible part triggers live visual warning and disables checkout until resolved.
- [x] Wattage meter updates dynamically with color-coded headroom bar.
- [x] "Proceed to Checkout" carries chosen parts directly to the cashier.

**Verification:**
- [x] Feature tests verifying checkout payload formulation and API response bindings.

**Dependencies:** Task 1, Task 2

**Files likely touched:**
- `resources/views/builder/index.blade.php`
- `public/js/builder.js`
- `public/css/style.css`

**Estimated scope:** Large (3 files)

---

## Task 4: Interactive Bottleneck & Game FPS Calculator
**Description:**
- Implement `resources/views/calculator/index.blade.php`:
  - Visual selector for CPU and GPU with search filter.
  - Resolution selector pills: 1080p (FHD), 1440p (2K), 2160p (4K).
  - Dynamic synergy score dial/meter (e.g. 95% Optimal Balance).
  - Game FPS estimate cards (Cyberpunk 2077, Call of Duty, Valorant, Apex Legends).
  - Direct "Build with this Combo" button leading into the PC Builder with CPU/GPU pre-selected.
- Connect AJAX calculation to `/api/compatibility/bottleneck`.

**Acceptance criteria:**
- [x] Calculator renders CPU and GPU selection.
- [x] Switching resolution or hardware updates synergy score and FPS estimates.
- [x] "Build with this Combo" links directly into builder.

**Verification:**
- [x] Feature tests and AJAX endpoint assertions.

**Dependencies:** Task 1, Task 2

**Files likely touched:**
- `resources/views/calculator/index.blade.php`

**Estimated scope:** Medium (1 file)

---

## Task 5: Automated Feature Tests (`FrontendStorefrontTest`)
**Description:**
- Create `tests/Feature/FrontendStorefrontTest.php` covering:
  - Homepage render with featured builds and review data.
  - PC Builder loading catalog items.
  - Builder submission transferring cart to checkout.
  - Bottleneck calculator view and calculation endpoint.
  - Contact page rendering.
- Run `php artisan test` across all test suites to confirm 100% pass rate.

**Acceptance criteria:**
- [x] All storefront tests pass.
- [x] Zero regressions across existing modules (54+ tests passing).

**Verification:**
- [x] `php artisan test` passes with 0 errors.

**Dependencies:** Task 1, Task 2, Task 3, Task 4

**Files likely touched:**
- `tests/Feature/FrontendStorefrontTest.php`

**Estimated scope:** Medium (1 file)

---

## Checkpoint: Complete (`frontend-storefront`)
- [x] All acceptance criteria in `SPEC-frontend-storefront.md` met
- [x] `php artisan migrate:fresh --seed` and `php artisan test` pass 100%
- [x] Ready for human review
