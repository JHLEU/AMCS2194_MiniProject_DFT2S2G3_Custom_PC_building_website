# Specification: Module 7 — `frontend-storefront`

## 1. Objective
Build the public customer storefront for the custom PC platform, delivering an intuitive, responsive, and interactive shopping experience:
1. **Homepage (`/`)**: Hero banner, curated pre-built gaming rigs, hardware category navigation, customer reviews carousel, and store value propositions.
2. **Interactive Custom PC Builder (`/builder`)**: Step-by-step hardware component selection (CPU, Motherboard, RAM, GPU, Storage, PSU, Cooler, Case) integrated in real-time with the `CompatibilityService` via AJAX, live wattage headroom bar, running price calculation, and one-click transfer to `/checkout`.
3. **CPU & GPU Bottleneck Calculator (`/calculator`)**: Customer-facing interactive calculator estimating resolution-dependent system synergy (1080p, 1440p, 4K), game FPS estimates, and hardware balance insights.
4. **Contact & Walk-in Workshop Info (`/contact`)**: Direct WhatsApp link, store hotline, operating hours, and service location.

---

## 2. Architecture & Tech Stack
- **Framework**: Laravel 12 + Blade Templates.
- **Styling**: Pure custom CSS (`public/css/style.css`), dark tech/gaming aesthetic, responsive flex/grid layouts.
- **Client Scripting**: Vanilla JavaScript + jQuery 3.7.1 (`public/js/vendor/jquery-3.7.1.min.js`), no npm/Vite compile step required.
- **APIs Used**:
  - `POST /api/compatibility/evaluate`: Validates selected components in builder, returns wattage headroom, error/warning badges.
  - `GET /api/compatibility/compatible-parts`: Dynamically returns filtered hardware options for a specific component category based on current selections.
  - `POST /api/compatibility/bottleneck`: Calculates synergy %, FPS estimates, and bottlenecks.

---

## 3. Endpoints & Controllers

### 3.1 `HomeController` (`App\Http\Controllers\HomeController`)
- `index()`:
  - Loads featured pre-built PCs.
  - Hardware categories with item counts and starting prices.
  - Testimonial data (5-star customer reviews).
  - Renders `home.blade.php`.

### 3.2 `PcBuilderController` (`App\Http\Controllers\PcBuilderController`)
- `index()`:
  - Loads initial hardware catalog grouped by category (`CPU`, `Motherboard`, `RAM`, `GPU`, `Storage`, `PSU`, `Cooling`, `Case`).
  - Renders `builder.blade.php` with visual component slots, live summary sidebar, and checkout payload generation.
- `buildToCart(Request $request)`:
  - Validates selected product IDs.
  - Formulates session cart data and redirects directly to `checkout.index`.

### 3.3 `CalculatorController` (`App\Http\Controllers\CalculatorController`)
- `index()`:
  - Loads available CPUs and GPUs.
  - Renders `calculator.blade.php`.
- `calculate(Request $request)`:
  - Accepts `cpu_id`, `gpu_id`, `resolution` (`1080p`, `1440p`, `4K`).
  - Calls `CompatibilityService::calculateBottleneck()` and returns JSON.

### 3.4 `ContactController` (`App\Http\Controllers\ContactController`)
- `index()`: Renders `contact.blade.php`.

---

## 4. UI / UX Design Specifications

### 4.1 PC Builder Configurator
- **Layout**: 2-Column Desktop layout (Component slot cards on the left, sticky Build Summary & Power Meter on the right). Mobile responsive stack.
- **Slot Component Card**:
  - Shows category icon + title (e.g. `Processor (CPU)`).
  - Selected item card preview (Brand, model name, price, specs pill).
  - "Choose Component" / "Change Component" modal or inline selector.
  - "Remove" button.
- **Sticky Summary Panel**:
  - Real-time Subtotal price counter (e.g. `RM 5,420.00`).
  - Compatibility Status Banner:
    - Green: `✔ 100% Compatible (Ready to Assemble)`
    - Red: `⚠️ Incompatibility Detected (e.g. AM5 CPU requires AM5 Motherboard)`
  - Power & Wattage Meter:
    - Estimated draw vs Selected PSU wattage with color-coded headroom bar (>25% Green, 15-25% Yellow, <15% Red).
  - Bottleneck Synergy Score preview.
  - CTA Button: `🛒 Proceed to Checkout with this Build`.

### 4.2 Customer Reviews Carousel
- Displays 5-star Google reviews from verified buyers.
- Interactive prev/next carousel controls using plain JavaScript.

---

## 5. Verification & Acceptance Criteria
1. Feature tests in `tests/Feature/FrontendStorefrontTest.php`:
   - `GET /`: Homepage renders with featured builds, categories, and reviews.
   - `GET /builder`: Configurator loads all component categories.
   - `POST /builder/checkout`: Submitting a selected build redirects to `/checkout` with the chosen items pre-filled in session.
   - `GET /calculator` & `POST /calculator/evaluate`: Bottleneck calculator loads and calculates synergy for selected hardware.
   - `GET /contact`: Contact page renders with store information and direct WhatsApp link.
2. Full test suite (`php artisan test`) passes with 100% success rate.
