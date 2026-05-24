@extends('layouts.app', ['title' => 'Customer Reviews'])

@section('content')
    <style>
        .public-reviews-page {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .public-home-header {
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 0;
            background: #F8FAFC;
            border: none;
            border-radius: 0;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            margin: -28px calc(50% - 50vw) 16px;
        }

        .public-home-header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 28px;
            min-height: 68px;
        }

        .public-home-brand {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            flex-shrink: 0;
            line-height: 0;
        }

        .public-home-logo {
            display: block;
            width: auto;
            height: 40px;
        }

        .public-home-nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 32px;
            flex: 1;
            flex-wrap: wrap;
        }

        .public-home-nav-link {
            color: #5E7288;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            padding: 4px 0;
            border-bottom: 2px solid transparent;
            transition: color 0.15s ease, border-color 0.15s ease;
        }

        .public-home-nav-link:hover,
        .public-home-nav-link.is-active {
            color: #123A5A;
            border-bottom-color: #F4D000;
            text-decoration: none;
        }

        .public-home-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .public-home-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: auto;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
        }

        .public-home-btn:hover {
            text-decoration: none;
            transform: none;
        }

        .public-home-btn-secondary {
            background: transparent;
            color: #123A5A;
            border: 1.5px solid #DDE7EE;
        }

        .public-home-btn-secondary:hover {
            background: rgba(16, 42, 67, 0.04);
            border-color: #123A5A;
        }

        .public-home-btn-primary {
            background: #123A5A;
            color: #ffffff;
            border: 1.5px solid #123A5A;
            font-weight: 600;
        }

        .public-home-btn-primary:hover {
            background: #0F2F4A;
            color: #ffffff;
        }

        .public-reviews-hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at top right, rgba(212, 160, 23, 0.18), transparent 28%),
                linear-gradient(135deg, #F8FAFC 0%, #F8FAFC 48%, #fff8e7 100%);
            border-color: #DDE7EE;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }

        .public-reviews-hero::before {
            content: "";
            position: absolute;
            top: -60px;
            right: -48px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, rgba(14, 165, 233, 0) 72%);
            pointer-events: none;
        }

        .public-reviews-hero-inner {
            position: relative;
            z-index: 1;
        }

        .public-reviews-hero-inner {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
        }

        .public-reviews-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid rgba(212, 160, 23, 0.24);
            color: #123A5A;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .public-reviews-eyebrow::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #F4D000;
            display: inline-block;
        }

        .public-reviews-subtitle {
            max-width: 700px;
            margin: 0;
            color: #5E7288;
            font-size: 15px;
            line-height: 1.8;
        }

        .public-reviews-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: 28px;
        }

        .public-reviews-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .public-reviews-filter-btn {
            appearance: none;
            border: 1px solid #DDE7EE;
            background: #ffffff;
            color: #20A7C9;
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
        }

        .public-reviews-filter-btn:hover {
            border-color: #7DDFF2;
            transform: translateY(-1px);
        }

        .public-reviews-filter-btn.is-active {
            background: #123A5A;
            color: #ffffff;
            border-color: #123A5A;
        }

        .public-reviews-filter-btn:focus-visible,
        .public-reviews-refresh:focus-visible,
        .public-review-image-link:focus-visible {
            outline: 3px solid rgba(14, 165, 233, 0.28);
            outline-offset: 3px;
        }

        .public-reviews-refresh {
            appearance: none;
            border: none;
            background: linear-gradient(135deg, #123A5A 0%, #20A7C9 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 12px 18px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 12px 28px rgba(16, 42, 67, 0.14);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .public-reviews-refresh:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(16, 42, 67, 0.18);
        }

        .public-reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 24px;
            align-items: start;
        }

        .public-review-card {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 24px;
            border: 1px solid #d9e6f2;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
        }

        .public-review-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .public-review-customer {
            min-width: 0;
        }

        .public-review-customer-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            flex: 1;
        }

        .public-review-avatar {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            border: 1px solid #DDE7EE;
            background: linear-gradient(135deg, #EAF9FD 0%, #FFF4C2 100%);
            color: #123A5A;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        }

        .public-review-avatar.has-image {
            background: #EAF0FB;
        }

        .public-review-avatar-image {
            display: none;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .public-review-avatar.has-image .public-review-avatar-image {
            display: block;
        }

        .public-review-avatar.has-image .public-review-avatar-fallback {
            display: none;
        }

        .public-review-avatar-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .public-review-customer-name {
            margin: 0 0 4px;
            color: #123A5A;
            font-size: 18px;
            line-height: 1.25;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .public-review-customer-meta {
            margin: 0;
            color: #5E7288;
            font-size: 13px;
        }

        .public-review-rating {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #FFF7CC;
            color: #123A5A;
            flex-shrink: 0;
        }

        .public-review-stars {
            display: inline-flex;
            gap: 2px;
            color: #F4D000;
            font-size: 14px;
            line-height: 1;
        }

        .public-review-rating-value {
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .public-review-message {
            margin: 0;
            color: #0F2F4A;
            font-size: 14px;
            line-height: 1.85;
        }

        .public-review-image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }

        .public-review-image-link,
        .public-review-image-empty {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #DDE7EE;
            background: #F8FAFC;
        }

        .public-review-image-link {
            display: block;
            min-height: 180px;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.24);
        }

        .public-review-image-link img {
            display: block;
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #e6f0fb;
            transition: transform 0.24s ease;
        }

        .public-review-image-link:hover img {
            transform: scale(1.03);
        }

        .public-review-image-empty {
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
            color: #829ab1;
            font-size: 13px;
            text-align: center;
        }

        .public-review-empty {
            padding: 48px 24px;
            text-align: center;
            border: 1px dashed #cfdbe8;
            border-radius: 20px;
            background: linear-gradient(180deg, #fcfeff 0%, #f7fbff 100%);
            color: #5E7288;
        }

        .public-review-empty-illustration {
            width: 76px;
            height: 76px;
            margin: 0 auto 18px;
            border-radius: 24px;
            background:
                radial-gradient(circle at top, rgba(212, 160, 23, 0.92) 0%, rgba(212, 160, 23, 0.38) 34%, rgba(212, 160, 23, 0) 62%),
                linear-gradient(180deg, #EAF9FD 0%, #ffffff 100%);
            box-shadow: inset 0 0 0 1px rgba(212, 160, 23, 0.14);
        }

        .public-review-empty strong {
            display: block;
            margin-bottom: 8px;
            color: #123A5A;
            font-size: 18px;
        }

        @media (max-width: 760px) {
            .public-home-header-inner,
            .public-reviews-hero-inner,
            .public-reviews-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .public-home-nav,
            .public-home-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .public-home-header {
                position: relative;
                margin: -28px -18px 16px;
            }

            .public-reviews-grid {
                grid-template-columns: 1fr;
            }

            .public-reviews-refresh {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 560px) {
            .public-home-header-inner {
                padding: 16px;
                min-height: auto;
            }

            .public-review-image-grid {
                grid-template-columns: 1fr;
            }

            .public-review-card-top {
                flex-direction: column;
                align-items: flex-start;
            }

            .public-review-card {
                padding: 18px;
            }

            .public-review-avatar {
                width: 40px;
                height: 40px;
            }
        }
    </style>

    <div class="public-reviews-page">
        @guest
            <header class="card public-home-header" aria-label="Public site header">
                <div class="public-home-header-inner">
                    <a href="{{ route('home') }}" class="public-home-brand" aria-label="RDY home">
                        <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="public-home-logo">
                    </a>

                    <nav class="public-home-nav" aria-label="Public navigation">
                        <a href="{{ route('home') }}#rdy" class="public-home-nav-link">RDY</a>
                        <a href="{{ route('home') }}#services" class="public-home-nav-link">Services</a>
                        <a href="{{ route('home') }}#news" class="public-home-nav-link">News</a>
                        <a href="{{ route('public.testimonies') }}" class="public-home-nav-link is-active">All Reviews</a>
                        <a href="{{ route('home') }}#about" class="public-home-nav-link">About</a>
                        <a href="{{ route('public.contact') }}" class="public-home-nav-link">Contact</a>
                        <a href="{{ route('home') }}#download-app" class="public-home-nav-link">Download App</a>
                    </nav>

                    <div class="public-home-actions">
                        <a href="{{ route('login') }}" class="public-home-btn public-home-btn-secondary">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="public-home-btn public-home-btn-primary">Register</a>
                        @endif
                    </div>
                </div>
            </header>
        @endguest

        <div class="card public-reviews-hero">
        <div class="public-reviews-hero-inner">
            <div>
                <div class="public-reviews-eyebrow">SolMate Experiences</div>
                <h1 class="page-title">Customer Reviews</h1>
                <p class="public-reviews-subtitle">See what customers share about their SolMate experience.</p>
            </div>
            <button id="refresh-public-testimonies-button" type="button" class="public-reviews-refresh">Refresh Reviews</button>
        </div>

        <div class="public-reviews-toolbar">
            <div class="public-reviews-filters" aria-label="Review filters">
                <button type="button" class="public-reviews-filter-btn is-active" data-review-filter="all">All</button>
                <button type="button" class="public-reviews-filter-btn" data-review-filter="with-images">With Images</button>
                <button type="button" class="public-reviews-filter-btn" data-review-filter="rating-5">&#9733; 5 Stars</button>
                <button type="button" class="public-reviews-filter-btn" data-review-filter="rating-4">&#9733; 4 Stars</button>
                <button type="button" class="public-reviews-filter-btn" data-review-filter="rating-3">&#9733; 3 Stars</button>
                <button type="button" class="public-reviews-filter-btn" data-review-filter="rating-2">&#9733; 2 Stars</button>
                <button type="button" class="public-reviews-filter-btn" data-review-filter="rating-1">&#9733; 1 Star</button>
            </div>
            <div id="public-testimonies-loading" class="info-box" style="margin-bottom: 0;">Loading approved reviews...</div>
        </div>

        <div id="public-testimonies-error" class="error-box" style="display: none; margin-top: 16px;"></div>
        </div>

        <div class="card">
            <div id="public-testimonies-empty" class="public-review-empty" style="display: none;">
                <div class="public-review-empty-illustration" aria-hidden="true"></div>
                <strong id="public-testimonies-empty-title">No approved reviews yet.</strong>
                <span id="public-testimonies-empty-copy">Customer feedback will appear here once the first public reviews are approved.</span>
            </div>

            <div id="public-testimonies-list" class="public-reviews-grid" style="display: none;"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const loadingBox = document.getElementById('public-testimonies-loading');
        const errorBox = document.getElementById('public-testimonies-error');
        const emptyState = document.getElementById('public-testimonies-empty');
        const listContainer = document.getElementById('public-testimonies-list');
        const refreshButton = document.getElementById('refresh-public-testimonies-button');
        const emptyTitle = document.getElementById('public-testimonies-empty-title');
        const emptyCopy = document.getElementById('public-testimonies-empty-copy');
        const filterButtons = Array.from(document.querySelectorAll('[data-review-filter]'));

        let allApprovedTestimonies = [];
        let currentFilter = 'all';

        function setVisible(element, visible, displayValue = 'block') {
            element.style.display = visible ? displayValue : 'none';
        }

        function showError(message) {
            errorBox.textContent = message;
            setVisible(errorBox, true);
        }

        function clearError() {
            errorBox.textContent = '';
            setVisible(errorBox, false);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatDate(value, options = {}) {
            if (!value) return 'Not available';

            const parsedDate = new Date(value);
            if (Number.isNaN(parsedDate.getTime())) return value;

            return parsedDate.toLocaleDateString(undefined, {
                year: 'numeric',
                month: options.compact ? 'short' : 'long',
                day: 'numeric',
            });
        }

        function getCustomerName(testimony) {
            return testimony?.user?.name || 'Anonymous customer';
        }

        function getCustomerInitials(testimony) {
            const name = getCustomerName(testimony).trim();
            if (!name) {
                return 'AC';
            }

            const parts = name.split(/\s+/).filter(Boolean);
            return parts
                .slice(0, 2)
                .map((part) => part.charAt(0))
                .join('')
                .toUpperCase() || 'AC';
        }

        function getCustomerProfileImageUrl(testimony) {
            return testimony?.user?.profile_picture_url
                || testimony?.user?.avatar
                || testimony?.user?.profileImage
                || testimony?.user?.photo_url
                || testimony?.user?.photoUrl
                || null;
        }

        function getCustomerAvatarMarkup(testimony) {
            const customerName = getCustomerName(testimony);
            const avatarUrl = getCustomerProfileImageUrl(testimony);
            const initials = getCustomerInitials(testimony);

            return `
                <div class="public-review-avatar${avatarUrl ? ' has-image' : ''}" aria-hidden="true">
                    ${avatarUrl ? `<img class="public-review-avatar-image" src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(customerName)} profile picture" loading="lazy" decoding="async" referrerpolicy="no-referrer">` : ''}
                    <span class="public-review-avatar-fallback">${escapeHtml(initials)}</span>
                </div>
            `;
        }

        function getDisplayDate(testimony) {
            return testimony?.created_at || testimony?.updated_at || null;
        }

        function getReviewImages(testimony) {
            return Array.isArray(testimony?.images)
                ? testimony.images.filter((image) => image && image.image_url)
                : [];
        }

        function getRatingMarkup(rating) {
            const numericRating = Number(rating);
            if (!Number.isFinite(numericRating) || numericRating <= 0) {
                return '';
            }

            const rounded = Math.max(0, Math.min(5, Math.round(numericRating)));
            let stars = '';

            for (let index = 0; index < 5; index += 1) {
                stars += `<span aria-hidden="true">${index < rounded ? '&#9733;' : '&#9734;'}</span>`;
            }

            return `
                <div class="public-review-rating" aria-label="Rating: ${escapeHtml(String(rounded))} out of 5">
                    <span class="public-review-stars">${stars}</span>
                    <span class="public-review-rating-value">${escapeHtml(String(rounded))}/5</span>
                </div>
            `;
        }

        function sortLatestFirst(testimonies) {
            return [...testimonies].sort((first, second) => {
                const firstDate = new Date(getDisplayDate(first) || 0).getTime();
                const secondDate = new Date(getDisplayDate(second) || 0).getTime();
                return secondDate - firstDate;
            });
        }

        function applyFilter(testimonies) {
            if (currentFilter === 'with-images') {
                return testimonies.filter((testimony) => getReviewImages(testimony).length > 0);
            }

            const ratingMatch = currentFilter.match(/^rating-(\d)$/);
            if (ratingMatch) {
                const targetRating = parseInt(ratingMatch[1], 10);
                return testimonies.filter((testimony) => Math.round(Number(testimony.rating)) === targetRating);
            }

            return testimonies;
        }

        function updateEmptyState(totalCount, filteredCount) {
            if (totalCount === 0) {
                emptyTitle.textContent = 'No approved reviews yet.';
                emptyCopy.textContent = 'Customer feedback will appear here once the first public reviews are approved.';
                return;
            }

            if (filteredCount === 0 && currentFilter === 'with-images') {
                emptyTitle.textContent = 'No approved photo reviews yet.';
                emptyCopy.textContent = 'Try the All filter to read approved customer feedback without uploaded images.';
                return;
            }

            const ratingMatch = currentFilter.match(/^rating-(\d)$/);
            if (filteredCount === 0 && ratingMatch) {
                emptyTitle.textContent = 'No reviews found for this rating yet.';
                emptyCopy.textContent = 'Try a different star filter or click All to see every approved review.';
                return;
            }

            emptyTitle.textContent = 'No approved reviews yet.';
            emptyCopy.textContent = 'Customer feedback will appear here once the first public reviews are approved.';
        }

        function renderTestimonies(testimonies) {
            const filtered = applyFilter(testimonies);
            updateEmptyState(testimonies.length, filtered.length);

            if (filtered.length === 0) {
                listContainer.innerHTML = '';
                setVisible(listContainer, false);
                setVisible(emptyState, true);
                return;
            }

            listContainer.innerHTML = filtered.map((testimony) => {
                const images = getReviewImages(testimony);
                const imageMarkup = images.length > 0
                    ? `
                        <div class="public-review-image-grid">
                            ${images.slice(0, 4).map((image, index) => `
                                <a class="public-review-image-link" href="${escapeHtml(image.image_url)}" target="_blank" rel="noreferrer">
                                    <img src="${escapeHtml(image.image_url)}" alt="Review image ${index + 1} from ${escapeHtml(getCustomerName(testimony))}">
                                </a>
                            `).join('')}
                        </div>
                    `
                    : `
                        <div class="public-review-image-empty">
                            No uploaded image for this review.
                        </div>
                    `;

                return `
                    <article class="public-review-card">
                        <div class="public-review-card-top">
                            <div class="public-review-customer-wrap">
                                ${getCustomerAvatarMarkup(testimony)}
                                <div class="public-review-customer">
                                    <h2 class="public-review-customer-name">${escapeHtml(getCustomerName(testimony))}</h2>
                                    <p class="public-review-customer-meta">Submitted ${escapeHtml(formatDate(getDisplayDate(testimony)))}</p>
                                </div>
                            </div>
                            ${getRatingMarkup(testimony.rating)}
                        </div>

                        <p class="public-review-message">${escapeHtml(testimony.message || testimony.title || 'No written review was provided.')}</p>

                        ${imageMarkup}
                    </article>
                `;
            }).join('');

            listContainer.querySelectorAll('.public-review-avatar-image').forEach((image) => {
                image.addEventListener('error', () => {
                    const avatar = image.closest('.public-review-avatar');
                    if (avatar) {
                        avatar.classList.remove('has-image');
                    }

                    image.removeAttribute('src');
                }, {once: true});
            });

            setVisible(emptyState, false);
            setVisible(listContainer, true, 'grid');
        }

        function setActiveFilter(nextFilter) {
            currentFilter = nextFilter;
            filterButtons.forEach((button) => {
                const isActive = button.dataset.reviewFilter === nextFilter;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
            renderTestimonies(allApprovedTestimonies);
        }

        async function loadPublicTestimonies() {
            clearError();
            setVisible(loadingBox, true);

            try {
                const response = await fetch('/api/public/testimonies', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'Could not load approved reviews.');
                }

                const testimonies = Array.isArray(payload)
                    ? payload
                    : (Array.isArray(payload.data) ? payload.data : []);
                allApprovedTestimonies = sortLatestFirst(testimonies);
                renderTestimonies(allApprovedTestimonies);
            } catch (error) {
                allApprovedTestimonies = [];
                listContainer.innerHTML = '';
                setVisible(listContainer, false);
                setVisible(emptyState, false);
                showError(error.message || 'Could not load approved reviews.');
            } finally {
                setVisible(loadingBox, false);
            }
        }

        refreshButton.addEventListener('click', loadPublicTestimonies);
        filterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                setActiveFilter(button.dataset.reviewFilter || 'all');
            });
        });

        setActiveFilter('all');
        loadPublicTestimonies();
    </script>
@endpush
