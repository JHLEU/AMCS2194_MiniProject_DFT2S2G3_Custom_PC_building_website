<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Custom PC Building Platform')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('styles')
</head>
<body>
    <header style="background-color: var(--bg-darker); border-bottom: 1px solid var(--border-color); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; position: relative;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="{{ route('home') }}" class="gopc-brand-logo" title="GOPC">
                <span class="gopc-text-go">GO</span><span class="gopc-text-pc">PC</span><span class="gopc-dot"></span>
            </a>
        </div>

        <nav class="main-nav">
            <a href="{{ route('home') }}" class="main-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('builder.index') }}" class="main-nav-link {{ request()->routeIs('builder.*') ? 'active' : '' }}">PC Builder</a>
            <a href="{{ route('calculator.index') }}" class="main-nav-link {{ request()->routeIs('calculator.*') ? 'active' : '' }}">Performance Calc</a>
            <a href="{{ route('orders.track') }}" class="main-nav-link {{ request()->routeIs('orders.track') ? 'active' : '' }}">Track Order</a>
            <a href="{{ route('checkout.index') }}" class="main-nav-link {{ request()->routeIs('checkout.*') ? 'active' : '' }}">Checkout</a>
            @auth
                <a href="{{ route('customer.orders') }}" class="main-nav-link {{ request()->routeIs('customer.orders') ? 'active' : '' }}">My Orders</a>
                <a href="{{ route('customer.warranties.index') }}" class="main-nav-link {{ request()->routeIs('customer.warranties.*') ? 'active' : '' }}">My Warranties</a>
                <a href="{{ route('customer.support.index') }}" class="main-nav-link {{ request()->routeIs('customer.support.*') ? 'active' : '' }}">Support</a>
            @endauth
        </nav>

        <div style="display: flex; align-items: center; gap: 14px;">
            @auth
                @if(Auth::user()->isStaff())
                    <a href="{{ route('erp.dashboard') }}" class="btn btn-outline btn-sm">ERP Dashboard</a>
                @endif
                <!-- Default User Avatar -->
                <div title="{{ Auth::user()->name ?? Auth::user()->username }}" style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #1e293b, #334155); border: 2px solid var(--primary); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s, border-color 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.3);" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--text-light)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="display: inline; margin: 0;">
                    @csrf
                    <button type="submit" class="uiverse-logout-btn" title="Sign out">
                        <div class="uiverse-logout-sign">
                            <svg viewBox="0 0 512 512">
                                <path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path>
                            </svg>
                        </div>
                        <div class="uiverse-logout-text">Logout</div>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Login</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
            @endauth
        </div>
    </header>

    <main>
        @if(session('success'))
            <div style="max-width: 1200px; margin: 20px auto; padding: 0 20px;">
                <div class="alert alert-success alert-dismissible">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div style="max-width: 1200px; margin: 20px auto; padding: 0 20px;">
                <div class="alert alert-danger alert-dismissible">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="site-footer-inner">
            <!-- Row 1: Google Reviews (Big Interactive Widget) -->
            <div class="footer-top-review-banner">
                <div class="footer-google-widget">
                    <div class="footer-google-widget-top">
                        <div class="footer-google-badge">
                            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                            </svg>
                            <div>
                                <div style="display: flex; align-items: baseline; gap: 10px;">
                                    <span class="footer-google-rating-huge">4.9</span>
                                    <span class="footer-google-stars-big">★★★★★</span>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                                    <span>Google Customer Rating</span>
                                    <span>•</span>
                                    <span style="color: #10b981;">284 Verified Reviews</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Controls: Left/Right Carousel + Write a Review Modal Trigger -->
                        <div class="footer-google-controls">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-open-review-modal" style="display: flex; align-items: center; gap: 6px;">
                                <span>✍️</span> Write a Review
                            </button>
                            <button type="button" class="review-slider-btn" id="reviews-prev" title="Previous Reviews">‹</button>
                            <button type="button" class="review-slider-btn" id="reviews-next" title="Next Reviews">›</button>
                        </div>
                    </div>

                    <!-- Interactive Reviews Carousel -->
                    <div class="footer-reviews-carousel" id="reviews-carousel">
                        <!-- Review 1 -->
                        <div class="footer-review-card-interactive">
                            <div>
                                <div class="review-user-info">
                                    <div class="review-user-avatar" style="background: #2563eb;">K</div>
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Kevin Tan</div>
                                        <div style="color: #fbbc04; font-size: 0.8rem;">★★★★★</div>
                                    </div>
                                </div>
                                <p class="review-text">
                                    "Built an RTX 4080 Super workstation here. Cable management was exceptionally clean and temperatures stay frosty under heavy Unreal 5 rendering loads!"
                                </p>
                            </div>
                            <div class="review-card-footer">
                                <span>Verified Hardware Build</span>
                                <span>2 days ago</span>
                            </div>
                        </div>

                        <!-- Review 2 -->
                        <div class="footer-review-card-interactive">
                            <div>
                                <div class="review-user-info">
                                    <div class="review-user-avatar" style="background: #059669;">S</div>
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Syed Amirul</div>
                                        <div style="color: #fbbc04; font-size: 0.8rem;">★★★★★</div>
                                    </div>
                                </div>
                                <p class="review-text">
                                    "Technicians bench-tested the RAM timing and stress-tested my system before delivery. Great advice on matching the B650 motherboard with Ryzen 7 7800X3D."
                                </p>
                            </div>
                            <div class="review-card-footer">
                                <span>Custom Gaming Rig</span>
                                <span>1 week ago</span>
                            </div>
                        </div>

                        <!-- Review 3 -->
                        <div class="footer-review-card-interactive">
                            <div>
                                <div class="review-user-info">
                                    <div class="review-user-avatar" style="background: #d97706;">M</div>
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Marcus Wong</div>
                                        <div style="color: #fbbc04; font-size: 0.8rem;">★★★★★</div>
                                    </div>
                                </div>
                                <p class="review-text">
                                    "Transparent pricing with genuine serial numbers. Tracking status updated in real-time. Highest recommendation for custom PC builders in KL!"
                                </p>
                            </div>
                            <div class="review-card-footer">
                                <span>Verified Customer</span>
                                <span>2 weeks ago</span>
                            </div>
                        </div>

                        <!-- Review 4 -->
                        <div class="footer-review-card-interactive">
                            <div>
                                <div class="review-user-info">
                                    <div class="review-user-avatar" style="background: #9333ea;">A</div>
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">Aisyah Razak</div>
                                        <div style="color: #fbbc04; font-size: 0.8rem;">★★★★★</div>
                                    </div>
                                </div>
                                <p class="review-text">
                                    "Customer support helped optimize my budget on the Performance Calculator. The rig arrived safely packaged with zero issues."
                                </p>
                            </div>
                            <div class="review-card-footer">
                                <span>Interactive PC Builder</span>
                                <span>3 weeks ago</span>
                            </div>
                        </div>

                        <!-- Review 5 -->
                        <div class="footer-review-card-interactive">
                            <div>
                                <div class="review-user-info">
                                    <div class="review-user-avatar" style="background: #e11d48;">D</div>
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">David Chong</div>
                                        <div style="color: #fbbc04; font-size: 0.8rem;">★★★★★</div>
                                    </div>
                                </div>
                                <p class="review-text">
                                    "Excellent after-sales support! Fast warranty claim response and friendly staff at Low Yat workshop."
                                </p>
                            </div>
                            <div class="review-card-footer">
                                <span>Workshop Service</span>
                                <span>1 month ago</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for "Write a Review" -->
            <div class="review-modal-overlay" id="review-modal">
                <div class="review-modal-content">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-size: 1.15rem; color: #fff; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
                            <span>⭐</span> Write a Google Review
                        </h3>
                        <button type="button" id="btn-close-review-modal" style="background: none; border: none; color: var(--text-muted); font-size: 1.4rem; cursor: pointer;">&times;</button>
                    </div>
                    <form id="google-review-mock-form" onsubmit="event.preventDefault(); submitGoogleReview();">
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.85rem; color: var(--text-light); margin-bottom: 6px;">Your Name</label>
                            <input type="text" id="reviewer-name" class="form-control" placeholder="Enter your full name" required style="width: 100%; padding: 10px 12px; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 6px; color: #fff;">
                        </div>
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.85rem; color: var(--text-light); margin-bottom: 6px;">Star Rating</label>
                            <div id="interactive-star-picker" style="font-size: 1.5rem; color: #fbbc04; cursor: pointer; user-select: none;">
                                <span data-val="1">★</span><span data-val="2">★</span><span data-val="3">★</span><span data-val="4">★</span><span data-val="5">★</span>
                            </div>
                            <input type="hidden" id="selected-rating" value="5">
                        </div>
                        <div style="margin-bottom: 18px;">
                            <label style="display: block; font-size: 0.85rem; color: var(--text-light); margin-bottom: 6px;">Review Feedback</label>
                            <textarea id="reviewer-feedback" rows="3" class="form-control" placeholder="Tell us about your custom rig, assembly quality, or staff support..." required style="width: 100%; padding: 10px 12px; background: var(--bg-input); border: 1px solid var(--border-color); border-radius: 6px; color: #fff;"></textarea>
                        </div>
                        <div style="display: flex; justify-content: flex-end; gap: 10px;">
                            <button type="button" class="btn btn-outline btn-sm" id="btn-cancel-review">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Row 2: Workshop & Address and Contact Us (2 Columns) -->
            <div class="site-footer-grid">
                <div>
                    <div class="footer-col-title">Workshop & Address</div>
                    <ul class="footer-compact-list">
                        <li>
                            <span class="icon">📍</span>
                            <span>{{ $contactInfo['address'] ?? 'Plaza Low Yat, Kuala Lumpur' }}</span>
                        </li>
                        <li>
                            <span class="icon">🕒</span>
                            <span style="color: #10b981;">{{ $contactInfo['operating_hours'] ?? 'Mon–Sat: 10:00 AM – 8:00 PM' }}</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <div class="footer-col-title">Contact Us</div>
                    <ul class="footer-compact-list">
                        <li>
                            <span class="icon">💬</span>
                            <a href="{{ $contactInfo['whatsapp_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" style="color: #25D366; font-weight: 500;">
                                WhatsApp: {{ $contactInfo['whatsapp_phone'] ?? '+60 16-888 2345' }}
                            </a>
                        </li>
                        <li>
                            <span class="icon">📞</span>
                            <a href="tel:{{ str_replace(' ', '', $contactInfo['hotline'] ?? '+60388881234') }}">
                                Tel: {{ $contactInfo['hotline'] ?? '+60 3-8888 1234' }}
                            </a>
                        </li>
                        <li>
                            <span class="icon">✉️</span>
                            <a href="mailto:{{ $contactInfo['support_email'] ?? 'support@custompc.local' }}">
                                {{ $contactInfo['support_email'] ?? 'support@custompc.local' }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Row 3: Custom PC Enterprise & Workshop (Expanded bottom banner) -->
            <div class="footer-bottom-brand-banner">
                <a href="{{ route('home') }}" class="footer-brand-title-full">
                    <span>⚡</span>
                    <span>{{ $contactInfo['store_name'] ?? 'Custom PC Enterprise & Workshop' }}</span>
                </a>
                <p class="footer-desc" style="max-width: 900px;">
                    Professional custom gaming PCs and workstation solutions. Every rig is precision-built, bench-tested, stress-tested, and backed by comprehensive hardware warranty and technical customer support.
                </p>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="footer-bottom-bar">
                <div>
                    &copy; {{ date('Y') }} {{ $contactInfo['store_name'] ?? 'Custom PC Enterprise & Workshop' }}. All rights reserved.
                </div>
                <div class="footer-bottom-nav">
                    @auth
                        @if(Auth::user()->isStaff())
                            <a href="{{ route('erp.dashboard') }}" class="footer-bottom-link staff-link">ERP Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="footer-bottom-link staff-link">Staff Portal</a>
                    @endauth
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/vendor/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @include('partials.chat-widget')
    <script src="{{ asset('js/chat-widget.js') }}"></script>
    <script>
        // Google Reviews Interactive Controls
        document.addEventListener('DOMContentLoaded', function () {
            const carousel = document.getElementById('reviews-carousel');
            const prevBtn = document.getElementById('reviews-prev');
            const nextBtn = document.getElementById('reviews-next');
            const modal = document.getElementById('review-modal');
            const openModalBtn = document.getElementById('btn-open-review-modal');
            const closeModalBtn = document.getElementById('btn-close-review-modal');
            const cancelBtn = document.getElementById('btn-cancel-review');

            if (carousel && prevBtn && nextBtn) {
                prevBtn.addEventListener('click', function () {
                    carousel.scrollBy({ left: -320, behavior: 'smooth' });
                });
                nextBtn.addEventListener('click', function () {
                    carousel.scrollBy({ left: 320, behavior: 'smooth' });
                });
            }

            if (openModalBtn && modal) {
                openModalBtn.addEventListener('click', function () {
                    modal.classList.add('active');
                });
            }

            function closeModal() {
                if (modal) modal.classList.remove('active');
            }

            if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
            if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });
            }

            // Star Rating Picker
            const starPicker = document.getElementById('interactive-star-picker');
            const ratingInput = document.getElementById('selected-rating');
            if (starPicker && ratingInput) {
                const stars = starPicker.querySelectorAll('span');
                stars.forEach(star => {
                    star.addEventListener('click', function () {
                        const val = parseInt(this.getAttribute('data-val'));
                        ratingInput.value = val;
                        stars.forEach(s => {
                            const sVal = parseInt(s.getAttribute('data-val'));
                            s.style.opacity = sVal <= val ? '1' : '0.25';
                        });
                    });
                });
            }
        });

        function submitGoogleReview() {
            const nameInput = document.getElementById('reviewer-name');
            const feedbackInput = document.getElementById('reviewer-feedback');
            const ratingInput = document.getElementById('selected-rating');
            const carousel = document.getElementById('reviews-carousel');
            const modal = document.getElementById('review-modal');

            const name = nameInput.value.trim();
            const feedback = feedbackInput.value.trim();
            const rating = parseInt(ratingInput.value) || 5;

            if (!name || !feedback) return;

            const starsHtml = '★'.repeat(rating) + '☆'.repeat(5 - rating);
            const initial = name.charAt(0).toUpperCase();
            const colors = ['#2563eb', '#059669', '#d97706', '#9333ea', '#e11d48', '#0891b2'];
            const randomColor = colors[Math.floor(Math.random() * colors.length)];

            const newCard = document.createElement('div');
            newCard.className = 'footer-review-card-interactive';
            newCard.style.border = '1px solid var(--primary)';
            newCard.innerHTML = `
                <div>
                    <div class="review-user-info">
                        <div class="review-user-avatar" style="background: ${randomColor};">${initial}</div>
                        <div>
                            <div style="font-weight: 700; color: #fff; font-size: 0.9rem;">${escapeHtml(name)}</div>
                            <div style="color: #fbbc04; font-size: 0.8rem;">${starsHtml}</div>
                        </div>
                    </div>
                    <p class="review-text">"${escapeHtml(feedback)}"</p>
                </div>
                <div class="review-card-footer">
                    <span style="color: #10b981;">✓ Just Posted</span>
                    <span>Just now</span>
                </div>
            `;

            if (carousel) {
                carousel.prepend(newCard);
                carousel.scrollTo({ left: 0, behavior: 'smooth' });
            }

            // Reset and close
            nameInput.value = '';
            feedbackInput.value = '';
            if (modal) modal.classList.remove('active');
            alert('Thank you! Your Google review has been posted successfully.');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text;
            return div.innerHTML;
        }
    </script>
    @yield('scripts')
</body>
</html>
