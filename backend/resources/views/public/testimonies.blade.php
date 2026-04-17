@extends('layouts.app', ['title' => 'Public Testimonies'])

@section('content')
    <style>
        .public-testimony-list {
            display: grid;
            gap: 16px;
        }

        .public-testimony-card {
            padding: 20px;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            background: #fbfcfe;
        }

        .public-hero {
            background: linear-gradient(135deg, #f8fbff 0%, #eef7ff 100%);
        }

        .image-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }

        .image-tile {
            display: block;
            border: 1px solid #d9e2ec;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
        }

        .image-tile img {
            display: block;
            width: 100%;
            height: 140px;
            object-fit: cover;
            background: #e8f1fb;
        }

        .image-tile span {
            display: block;
            padding: 10px 12px;
            color: #334e68;
            font-size: 13px;
        }

        .empty-illustration {
            width: 68px;
            height: 68px;
            border-radius: 999px;
            background: #dbeafe;
            margin: 0 auto 16px;
        }
    </style>

    <div class="card public-hero">
        <div class="section-header">
            <div>
                <h1 class="page-title">Customer Testimonies</h1>
                <p class="page-copy">Read approved customer feedback shared publicly on the Solmate website.</p>
            </div>
            <div class="actions" style="margin-top: 0;">
                <button id="refresh-public-testimonies-button" type="button" class="secondary">Refresh</button>
            </div>
        </div>

        <div id="public-testimonies-loading" class="info-box">Loading approved testimonies...</div>
        <div id="public-testimonies-error" class="error-box" style="display: none;"></div>
    </div>

    <div class="card">
        <div id="public-testimonies-empty" class="info-box" style="display: none; margin-bottom: 0;">
            <div class="empty-illustration"></div>
            No approved testimonies are available yet.
        </div>

        <div id="public-testimonies-list" class="public-testimony-list" style="display: none;"></div>
    </div>
@endsection

@push('scripts')
    <script>
        const loadingBox = document.getElementById('public-testimonies-loading');
        const errorBox = document.getElementById('public-testimonies-error');
        const emptyState = document.getElementById('public-testimonies-empty');
        const listContainer = document.getElementById('public-testimonies-list');
        const refreshButton = document.getElementById('refresh-public-testimonies-button');

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

        function formatDate(value) {
            if (!value) {
                return 'Not available';
            }

            const parsedDate = new Date(value);

            if (Number.isNaN(parsedDate.getTime())) {
                return value;
            }

            return parsedDate.toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        }

        function getRatingLabel(rating) {
            return `${Number(rating || 0)}/5`;
        }

        function getCustomerName(testimony) {
            return testimony.user?.name || 'Anonymous customer';
        }

        function renderTestimonies(testimonies) {
            if (!Array.isArray(testimonies) || testimonies.length === 0) {
                listContainer.innerHTML = '';
                setVisible(listContainer, false);
                setVisible(emptyState, true);
                return;
            }

            listContainer.innerHTML = testimonies.map((testimony) => {
                const images = Array.isArray(testimony.images) ? testimony.images : [];
                const imageMarkup = images.length > 0
                    ? `
                        <div class="image-grid" style="margin-top: 16px;">
                            ${images.map((image, index) => {
                                const imageUrl = image.image_url || '';

                                if (!imageUrl) {
                                    return `
                                        <div class="image-tile">
                                            <div style="height: 140px; background: #e8f1fb;"></div>
                                            <span>Image ${index + 1} unavailable</span>
                                        </div>
                                    `;
                                }

                                return `
                                    <a class="image-tile" href="${escapeHtml(imageUrl)}" target="_blank" rel="noreferrer">
                                        <img src="${escapeHtml(imageUrl)}" alt="Public testimony image ${index + 1}">
                                        <span>Image ${index + 1}</span>
                                    </a>
                                `;
                            }).join('')}
                        </div>
                    `
                    : '';

                return `
                    <div class="public-testimony-card">
                        <div class="request-header">
                            <div>
                                <div class="request-title">${escapeHtml(testimony.title || 'Customer testimony')}</div>
                                <div class="muted">${escapeHtml(getCustomerName(testimony))}</div>
                            </div>
                            <div class="request-badges">
                                <span class="badge badge-success">Approved</span>
                                <span class="badge badge-primary">${escapeHtml(getRatingLabel(testimony.rating))}</span>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Date</span>
                                <strong>${escapeHtml(formatDate(testimony.updated_at || testimony.created_at))}</strong>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Images</span>
                                <strong>${escapeHtml(`${images.length} image${images.length === 1 ? '' : 's'}`)}</strong>
                            </div>
                        </div>

                        <div class="detail-box" style="margin-top: 16px;">
                            <h3 style="margin-bottom: 10px;">Message</h3>
                            <p class="detail-copy">${escapeHtml(testimony.message || '')}</p>
                        </div>

                        ${imageMarkup}
                    </div>
                `;
            }).join('');

            setVisible(emptyState, false);
            setVisible(listContainer, true);
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
                    throw new Error(payload.message || 'Could not load approved testimonies.');
                }

                renderTestimonies(Array.isArray(payload.data) ? payload.data : []);
            } catch (error) {
                listContainer.innerHTML = '';
                setVisible(listContainer, false);
                setVisible(emptyState, false);
                showError(error.message || 'Could not load approved testimonies.');
            } finally {
                setVisible(loadingBox, false);
            }
        }

        refreshButton.addEventListener('click', loadPublicTestimonies);
        loadPublicTestimonies();
    </script>
@endpush
