@extends('layouts.app', ['title' => 'Manage Promotions'])

@section('content')
    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Content Management</p>
        <h1 class="page-title">Homepage Promotions</h1>
        <p class="page-copy">Create and manage promotional banners that appear on the public homepage. Set start/end dates to schedule promotions automatically — expired promos are hidden without any manual action needed.</p>
    </div>

    <style>
        .promo-admin-panel {
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .promo-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
        }

        .promo-admin-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .promo-admin-thumb {
            aspect-ratio: 16 / 7;
            background: linear-gradient(135deg, #123A5A 0%, #1f4d76 56%, #20A7C9 100%);
            overflow: hidden;
            position: relative;
        }

        .promo-admin-thumb img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .promo-admin-thumb-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            text-align: center;
            line-height: 1.6;
        }

        .promo-admin-card-body {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
            flex: 1;
        }

        .promo-admin-card-title {
            margin: 0;
            color: #123A5A;
            font-size: 17px;
            font-weight: 700;
            line-height: 1.35;
        }

        .promo-admin-card-desc {
            margin: 0;
            color: #5E7288;
            font-size: 13px;
            line-height: 1.7;
        }

        .promo-admin-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .promo-admin-meta-item {
            padding: 12px 14px;
            border: 1px solid #DDE7EE;
            border-radius: 14px;
            background: #F8FAFC;
        }

        .promo-admin-meta-item.full-width {
            grid-column: 1 / -1;
        }

        .promo-admin-meta-label {
            display: block;
            margin-bottom: 4px;
            color: #7F92A3;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .promo-admin-meta-value {
            display: block;
            color: #123A5A;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.6;
            word-break: break-word;
        }

        .promo-admin-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .promo-admin-actions button {
            min-width: 110px;
        }

        .promo-expired-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid rgba(185,28,28,0.2);
        }

        .promo-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .promo-modal-overlay.open {
            display: flex;
        }

        .promo-modal {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 28px;
        }

        .promo-modal-title {
            font-size: 20px;
            font-weight: 800;
            color: #123A5A;
            margin: 0 0 20px;
        }

        .promo-image-preview {
            border: 1px dashed #DDE7EE;
            border-radius: 14px;
            overflow: hidden;
            background: #F8FAFC;
            margin-top: 8px;
        }

        .promo-image-preview img {
            width: 100%;
            aspect-ratio: 16 / 7;
            object-fit: cover;
            display: block;
        }

        .promo-image-preview-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 90px;
            padding: 16px;
            text-align: center;
            color: #5E7288;
            font-size: 13px;
        }
    </style>

    <div class="card admin-section-surface">
        <div id="promo-loading" class="info-box">Loading promotions...</div>
        <div id="promo-success" class="status" style="display:none;"></div>
        <div id="promo-error" class="error-box" style="display:none;"></div>

        <div id="promo-content" class="stack" style="display:none;">

            {{-- Add / Edit Form Panel --}}
            <div class="promo-admin-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 id="promo-form-heading" class="admin-section-title" style="margin: 0 0 6px;">Add Promotion</h2>
                        <div class="muted">Fill in the details below. Only active, in-date promotions appear on the homepage.</div>
                    </div>
                </div>

                <form id="promo-form" class="form-grid two-columns" style="margin-top: 18px;" enctype="multipart/form-data">
                    <input type="hidden" id="promo_edit_id" value="">

                    <div style="grid-column: 1 / -1;">
                        <label for="promo_title">Promo Title <span style="color:#e53e3e;">*</span></label>
                        <input id="promo_title" name="title" type="text" placeholder="e.g. Buy 5 Solar Panels, Get 1 Free">
                        <div class="field-error" data-promo-error-for="title"></div>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label for="promo_description">Short Description</label>
                        <textarea id="promo_description" name="description" rows="3" placeholder="Brief description shown on the promo card..."></textarea>
                        <div class="field-error" data-promo-error-for="description"></div>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <label for="promo_image">Banner / Image</label>
                        <input id="promo_image" name="image" type="file" accept="image/jpg,image/jpeg,image/png,image/webp">
                        <div class="field-error" data-promo-error-for="image"></div>
                        <div id="promo-image-preview" class="promo-image-preview" style="display:none;">
                            <img id="promo-image-preview-img" src="" alt="Banner preview">
                        </div>
                    </div>

                    <div>
                        <label for="promo_start_date">Start Date</label>
                        <input id="promo_start_date" name="start_date" type="date">
                        <div class="field-error" data-promo-error-for="start_date"></div>
                    </div>

                    <div>
                        <label for="promo_end_date">End Date</label>
                        <input id="promo_end_date" name="end_date" type="date">
                        <div class="field-error" data-promo-error-for="end_date"></div>
                    </div>

                    <div style="display:flex; align-items:center; gap:10px; grid-column: 1 / -1;">
                        <input id="promo_is_active" name="is_active" type="checkbox" checked style="width:18px; height:18px; flex-shrink:0;">
                        <label for="promo_is_active" style="margin:0; cursor:pointer;">Active (show on homepage)</label>
                    </div>

                    <div class="actions" style="grid-column: 1 / -1; gap: 10px;">
                        <button id="promo-submit" type="submit">Save Promotion</button>
                        <button id="promo-cancel-edit" type="button" class="secondary" style="display:none;">Cancel Edit</button>
                    </div>
                </form>
            </div>

            {{-- Existing Promotions --}}
            <div class="promo-admin-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Existing Promotions</h2>
                        <div class="muted">Toggle visibility, edit details, or delete promotions.</div>
                    </div>
                    <button id="promo-refresh" type="button" class="secondary">Refresh</button>
                </div>

                <div id="promo-empty" class="info-box" style="display:none; margin-top: 16px;">No promotions created yet.</div>
                <div id="promo-list" class="promo-admin-grid" style="display:none; margin-top: 16px;"></div>
            </div>

        </div>
    </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const loadingBox = document.getElementById('promo-loading');
    const successBox = document.getElementById('promo-success');
    const errorBox   = document.getElementById('promo-error');
    const content    = document.getElementById('promo-content');
    const form       = document.getElementById('promo-form');
    const submitBtn  = document.getElementById('promo-submit');
    const cancelBtn  = document.getElementById('promo-cancel-edit');
    const refreshBtn = document.getElementById('promo-refresh');
    const emptyState = document.getElementById('promo-empty');
    const list       = document.getElementById('promo-list');
    const formHeading = document.getElementById('promo-form-heading');
    const editIdInput = document.getElementById('promo_edit_id');
    const imageInput  = document.getElementById('promo_image');
    const imagePreview    = document.getElementById('promo-image-preview');
    const imagePreviewImg = document.getElementById('promo-image-preview-img');

    let promotions = [];

    /* ── helpers ─────────────────────────────────────────────── */

    function setVisible(el, visible, displayValue = 'block') {
        el.style.display = visible ? displayValue : 'none';
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
        if (!value) return '—';
        const d = new Date(value);
        return Number.isNaN(d.getTime()) ? value : d.toLocaleDateString();
    }

    function isExpired(promo) {
        if (!promo.end_date) return false;
        return new Date(promo.end_date) < new Date(new Date().toDateString());
    }

    function getCookie(name) {
        const prefix = `${name}=`;
        for (const part of document.cookie.split(';')) {
            const trimmed = part.trim();
            if (trimmed.startsWith(prefix)) {
                return decodeURIComponent(trimmed.slice(prefix.length));
            }
        }
        return null;
    }

    async function ensureCsrf() {
        await fetch('/sanctum/csrf-cookie', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
    }

    async function apiRequest(endpoint, options = {}) {
        const method = (options.method || 'GET').toUpperCase();
        const isMultipart = options.isMultipart === true;
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        if (method !== 'GET') {
            await ensureCsrf();
            if (!isMultipart) {
                headers['Content-Type'] = 'application/json';
            }
            headers['X-XSRF-TOKEN'] = getCookie('XSRF-TOKEN') || '';
        }

        const response = await fetch(endpoint, {
            method,
            credentials: 'same-origin',
            headers,
            body: options.body || undefined,
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            const err = new Error(payload.message || 'Request failed.');
            err.status = response.status;
            err.payload = payload;
            throw err;
        }

        return payload;
    }

    function clearFeedback() {
        successBox.textContent = '';
        errorBox.textContent = '';
        setVisible(successBox, false);
        setVisible(errorBox, false);
        document.querySelectorAll('[data-promo-error-for]').forEach((el) => {
            el.textContent = '';
        });
    }

    function showError(msg) { errorBox.textContent = msg; setVisible(errorBox, true); }
    function showSuccess(msg) { successBox.textContent = msg; setVisible(successBox, true); }

    /* ── image preview ───────────────────────────────────────── */

    imageInput.addEventListener('change', () => {
        const file = imageInput.files[0];
        if (file) {
            const url = URL.createObjectURL(file);
            imagePreviewImg.src = url;
            setVisible(imagePreview, true);
        } else {
            setVisible(imagePreview, false);
        }
    });

    /* ── rendering ───────────────────────────────────────────── */

    function renderList() {
        if (!Array.isArray(promotions) || promotions.length === 0) {
            list.innerHTML = '';
            setVisible(list, false);
            setVisible(emptyState, true);
            return;
        }

        list.innerHTML = promotions.map((promo) => {
            const expired = isExpired(promo);
            const statusClass = promo.is_active ? 'badge badge-success' : 'badge badge-warning';
            const statusLabel = promo.is_active ? 'Active' : 'Inactive';
            const mediaHtml = promo.image_url
                ? `<img src="${escapeHtml(promo.image_url)}" alt="${escapeHtml(promo.title)} banner">`
                : `<div class="promo-admin-thumb-placeholder">No banner uploaded yet.</div>`;
            return `
                <article class="promo-admin-card">
                    <div class="promo-admin-thumb">${mediaHtml}</div>
                    <div class="promo-admin-card-body">
                        <div class="actions" style="justify-content: space-between; align-items: flex-start; margin: 0; gap: 8px; flex-wrap: wrap;">
                            <span class="${statusClass}">${statusLabel}</span>
                            ${expired ? '<span class="promo-expired-badge">Expired</span>' : ''}
                        </div>

                        <h3 class="promo-admin-card-title">${escapeHtml(promo.title)}</h3>
                        ${promo.description ? `<p class="promo-admin-card-desc">${escapeHtml(promo.description)}</p>` : ''}

                        <div class="promo-admin-meta">
                            <div class="promo-admin-meta-item">
                                <span class="promo-admin-meta-label">Start Date</span>
                                <span class="promo-admin-meta-value">${escapeHtml(formatDate(promo.start_date))}</span>
                            </div>
                            <div class="promo-admin-meta-item">
                                <span class="promo-admin-meta-label">End Date</span>
                                <span class="promo-admin-meta-value">${escapeHtml(formatDate(promo.end_date))}</span>
                            </div>
                        </div>

                        <div class="promo-admin-actions">
                            <button type="button" data-promo-action="toggle" data-promo-id="${promo.id}">${promo.is_active ? 'Set Inactive' : 'Set Active'}</button>
                            <button type="button" class="secondary" data-promo-action="edit" data-promo-id="${promo.id}">Edit</button>
                            <button type="button" class="danger" data-promo-action="delete" data-promo-id="${promo.id}">Delete</button>
                        </div>
                    </div>
                </article>
            `;
        }).join('');

        setVisible(emptyState, false);
        setVisible(list, true, 'grid');
    }

    /* ── load ────────────────────────────────────────────────── */

    async function loadPromotions() {
        clearFeedback();
        setVisible(loadingBox, true);
        setVisible(content, false);

        try {
            const payload = await apiRequest('/api/admin/promotions');
            promotions = Array.isArray(payload.data) ? payload.data : [];
            renderList();
            setVisible(content, true);
        } catch (error) {
            showError(error.message || 'Could not load promotions.');
        } finally {
            setVisible(loadingBox, false);
        }
    }

    /* ── reset form ──────────────────────────────────────────── */

    function resetForm() {
        form.reset();
        editIdInput.value = '';
        document.getElementById('promo_is_active').checked = true;
        setVisible(imagePreview, false);
        formHeading.textContent = 'Add Promotion';
        submitBtn.textContent = 'Save Promotion';
        setVisible(cancelBtn, false);
    }

    /* ── populate form for editing ───────────────────────────── */

    function populateForm(promo) {
        editIdInput.value = promo.id;
        document.getElementById('promo_title').value = promo.title ?? '';
        document.getElementById('promo_description').value = promo.description ?? '';
        document.getElementById('promo_start_date').value = promo.start_date ? promo.start_date.slice(0, 10) : '';
        document.getElementById('promo_end_date').value = promo.end_date ? promo.end_date.slice(0, 10) : '';
        document.getElementById('promo_is_active').checked = !!promo.is_active;

        if (promo.image_url) {
            imagePreviewImg.src = promo.image_url;
            setVisible(imagePreview, true);
        } else {
            setVisible(imagePreview, false);
        }

        formHeading.textContent = 'Edit Promotion';
        submitBtn.textContent = 'Update Promotion';
        setVisible(cancelBtn, true);

        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /* ── submit ──────────────────────────────────────────────── */

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFeedback();

        const editId = editIdInput.value;
        const isEdit = !!editId;
        const endpoint = isEdit ? `/api/admin/promotions/${editId}` : '/api/admin/promotions';

        submitBtn.disabled = true;
        submitBtn.textContent = isEdit ? 'Updating...' : 'Saving...';

        try {
            const formData = new FormData(form);
            // FormData checkbox: only present when checked
            formData.set('is_active', document.getElementById('promo_is_active').checked ? '1' : '0');

            const payload = await apiRequest(endpoint, {
                method: 'POST',
                isMultipart: true,
                body: formData,
            });

            resetForm();
            showSuccess(payload.message || 'Promotion saved successfully.');
            await loadPromotions();
        } catch (error) {
            if (error.status === 422 && error.payload?.errors) {
                Object.entries(error.payload.errors).forEach(([field, messages]) => {
                    const target = document.querySelector(`[data-promo-error-for="${field}"]`);
                    if (target && Array.isArray(messages) && messages[0]) {
                        target.textContent = messages[0];
                    }
                });
            }
            showError(error.message || 'Could not save the promotion.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = isEdit ? 'Update Promotion' : 'Save Promotion';
        }
    });

    /* ── cancel edit ─────────────────────────────────────────── */

    cancelBtn.addEventListener('click', resetForm);

    /* ── list actions ────────────────────────────────────────── */

    async function togglePromo(id) {
        clearFeedback();
        try {
            const payload = await apiRequest(`/api/admin/promotions/${id}/toggle`, { method: 'PATCH' });
            showSuccess(payload.message || 'Promotion updated.');
            await loadPromotions();
        } catch (error) {
            showError(error.message || 'Could not toggle the promotion.');
        }
    }

    function editPromo(id) {
        const promo = promotions.find((p) => p.id === id);
        if (promo) populateForm(promo);
    }

    async function deletePromo(id) {
        if (!window.confirm('Delete this promotion? This cannot be undone.')) return;
        clearFeedback();
        try {
            const payload = await apiRequest(`/api/admin/promotions/${id}`, { method: 'DELETE' });
            showSuccess(payload.message || 'Promotion deleted.');
            if (editIdInput.value === String(id)) resetForm();
            await loadPromotions();
        } catch (error) {
            showError(error.message || 'Could not delete the promotion.');
        }
    }

    refreshBtn.addEventListener('click', loadPromotions);

    list.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-promo-action]');
        if (!btn) return;

        const id = Number(btn.dataset.promoId);
        if (!Number.isFinite(id) || id <= 0) return;

        if (btn.dataset.promoAction === 'toggle') { togglePromo(id); return; }
        if (btn.dataset.promoAction === 'edit')   { editPromo(id);   return; }
        if (btn.dataset.promoAction === 'delete') { deletePromo(id); }
    });

    loadPromotions();
})();
</script>
@endpush
