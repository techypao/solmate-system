@extends('layouts.app', ['title' => 'Admin Visual Highlights'])

@section('content')
    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Admin Gallery</p>
        <h1 class="page-title">Manage Visual Highlights</h1>
        <p class="page-copy">Upload and manage the images shown in the public “See SolMate In Action” carousel. Only active items appear on the public site.</p>
    </div>

    <style>
        .visual-highlights-panel {
            padding: 20px;
            background: linear-gradient(180deg, #ffffff 0%, #F8FAFC 100%);
            border: 1px solid #DDE7EE;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .visual-highlights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .visual-highlight-card {
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .visual-highlight-card img,
        .visual-highlight-preview img {
            width: 100%;
            display: block;
            object-fit: cover;
        }

        .visual-highlight-card img {
            aspect-ratio: 16 / 10;
            background: #DDE7EE;
        }

        .visual-highlight-card-body {
            padding: 16px;
        }

        .visual-highlight-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .visual-highlight-badge.is-active {
            color: #166534;
            background: #dcfce7;
        }

        .visual-highlight-badge.is-inactive {
            color: #92400e;
            background: #FFF7CC;
        }

        .visual-highlight-preview {
            border: 1px dashed #DDE7EE;
            border-radius: 16px;
            overflow: hidden;
            background: #F8FAFC;
        }

        .visual-highlight-preview img {
            aspect-ratio: 16 / 9;
            background: #DDE7EE;
        }

        .visual-highlight-preview-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 220px;
            padding: 24px;
            text-align: center;
            color: #5E7288;
            font-size: 14px;
            line-height: 1.6;
        }

    </style>

    <div class="card admin-section-surface">
        <div id="visual-highlights-loading" class="info-box">Loading visual highlights...</div>
        <div id="visual-highlights-success" class="status" style="display: none;"></div>
        <div id="visual-highlights-error" class="error-box" style="display: none;"></div>

        <div id="visual-highlights-content" class="stack" style="display: none;">
            <div class="visual-highlights-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Add or Edit Carousel Image</h2>
                        <div class="muted">Upload a new image or update an existing visual highlight.</div>
                    </div>
                    <button id="visual-highlight-reset-button" type="button" class="secondary" style="display: none;">Cancel edit</button>
                </div>

                <form id="visual-highlight-form" class="form-grid two-columns" style="margin-top: 18px;">
                    <input id="visual_highlight_id" name="visual_highlight_id" type="hidden">

                    <div style="grid-column: 1 / -1;">
                        <label for="visual_highlight_image">Carousel image</label>
                        <input id="visual_highlight_image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <div class="field-error" data-visual-highlight-error-for="image"></div>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <div class="visual-highlight-preview">
                            <img id="visual-highlight-preview-image" src="" alt="Selected visual highlight preview" style="display: none;">
                            <div id="visual-highlight-preview-empty" class="visual-highlight-preview-empty">
                                Select an image to preview it here before saving.
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="visual_highlight_is_active" class="checkbox-inline">
                            <input id="visual_highlight_is_active" name="is_active" type="checkbox" checked>
                            <span>Show this image in the public carousel</span>
                        </label>
                        <div class="field-error" data-visual-highlight-error-for="is_active"></div>
                    </div>

                    <div class="actions" style="grid-column: 1 / -1;">
                        <button id="visual-highlight-save-button" type="submit">Upload image</button>
                        <button id="visual-highlight-delete-button" type="button" class="danger" style="display: none;">Delete image</button>
                        <span id="visual-highlight-form-hint" class="muted">New images are saved through the admin visual highlights API.</span>
                    </div>
                </form>
            </div>

            <div class="visual-highlights-panel">
                <div class="actions" style="justify-content: space-between;">
                    <div>
                        <h2 class="admin-section-title" style="margin: 0 0 6px;">Carousel Images</h2>
                        <div class="muted">Preview the exact images available to the public carousel and update them as needed.</div>
                    </div>
                    <button id="visual-highlights-refresh-button" type="button" class="secondary">Refresh</button>
                </div>

                <div id="visual-highlights-empty" class="info-box" style="display: none; margin-top: 16px;">No carousel images uploaded yet.</div>
                <div id="visual-highlights-list" class="visual-highlights-grid" style="display: none; margin-top: 16px;"></div>
            </div>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const loadingBox = document.getElementById('visual-highlights-loading');
            const successBox = document.getElementById('visual-highlights-success');
            const errorBox = document.getElementById('visual-highlights-error');
            const content = document.getElementById('visual-highlights-content');
            const form = document.getElementById('visual-highlight-form');
            const list = document.getElementById('visual-highlights-list');
            const emptyState = document.getElementById('visual-highlights-empty');
            const refreshButton = document.getElementById('visual-highlights-refresh-button');
            const resetButton = document.getElementById('visual-highlight-reset-button');
            const saveButton = document.getElementById('visual-highlight-save-button');
            const deleteButton = document.getElementById('visual-highlight-delete-button');
            const formHint = document.getElementById('visual-highlight-form-hint');
            const hiddenIdInput = document.getElementById('visual_highlight_id');
            const fileInput = document.getElementById('visual_highlight_image');
            const isActiveInput = document.getElementById('visual_highlight_is_active');
            const previewImage = document.getElementById('visual-highlight-preview-image');
            const previewEmpty = document.getElementById('visual-highlight-preview-empty');

            let visualHighlights = [];
            let editingId = null;
            let previewObjectUrl = null;

            function setVisible(element, visible, displayValue = 'block') {
                element.style.display = visible ? displayValue : 'none';
            }

            function getCookie(name) {
                const prefix = `${name}=`;
                const parts = document.cookie.split(';');

                for (const part of parts) {
                    const trimmed = part.trim();

                    if (trimmed.startsWith(prefix)) {
                        return decodeURIComponent(trimmed.substring(prefix.length));
                    }
                }

                return null;
            }

            async function ensureCsrfCookie() {
                await fetch('/sanctum/csrf-cookie', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            }

            function escapeHtml(value) {
                return String(value == null ? '' : value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function trimToNull(value) {
                const trimmed = String(value || '').trim();
                return trimmed === '' ? null : trimmed;
            }

            function clearMessages() {
                successBox.textContent = '';
                errorBox.textContent = '';
                setVisible(successBox, false);
                setVisible(errorBox, false);
            }

            function clearFieldErrors() {
                document.querySelectorAll('[data-visual-highlight-error-for]').forEach((element) => {
                    element.textContent = '';
                });
            }

            function showError(message) {
                errorBox.textContent = message;
                setVisible(errorBox, true);
            }

            function revokePreviewObjectUrl() {
                if (previewObjectUrl) {
                    URL.revokeObjectURL(previewObjectUrl);
                    previewObjectUrl = null;
                }
            }

            function setPreview(src, altText = 'Visual highlight preview') {
                if (!src) {
                    previewImage.src = '';
                    previewImage.alt = 'Selected visual highlight preview';
                    setVisible(previewImage, false);
                    setVisible(previewEmpty, true, 'flex');
                    return;
                }

                previewImage.src = src;
                previewImage.alt = altText;
                setVisible(previewImage, true);
                setVisible(previewEmpty, false);
            }

            function getEditingItem() {
                return visualHighlights.find((item) => item.id === editingId) || null;
            }

            function syncPreviewFromCurrentState() {
                const file = fileInput.files && fileInput.files[0];

                if (file) {
                    revokePreviewObjectUrl();
                    previewObjectUrl = URL.createObjectURL(file);
                    setPreview(previewObjectUrl, file.name);
                    return;
                }

                const editingItem = getEditingItem();
                setPreview(
                    editingItem ? editingItem.image_url : '',
                    'Current visual highlight preview'
                );
            }

            function resetForm() {
                editingId = null;
                hiddenIdInput.value = '';
                form.reset();
                isActiveInput.checked = true;
                saveButton.textContent = 'Upload image';
                formHint.textContent = 'New images are saved through the admin visual highlights API.';
                setVisible(resetButton, false);
                setVisible(deleteButton, false);
                clearFieldErrors();
                revokePreviewObjectUrl();
                setPreview('');
            }

            function fillForm(item) {
                editingId = item.id;
                hiddenIdInput.value = String(item.id);
                isActiveInput.checked = Boolean(item.is_active);
                fileInput.value = '';
                saveButton.textContent = 'Save changes';
                formHint.textContent = 'Replace the image only if needed. Leave the upload field empty to keep the current file.';
                setVisible(resetButton, true);
                setVisible(deleteButton, true);
                clearFieldErrors();
                revokePreviewObjectUrl();
                setPreview(item.image_url, 'Current visual highlight preview');
            }

            function renderList(items) {
                if (!Array.isArray(items) || items.length === 0) {
                    list.innerHTML = '';
                    setVisible(list, false);
                    setVisible(emptyState, true);
                    return;
                }

                setVisible(emptyState, false);
                list.innerHTML = items.map((item) => {
                    const statusClass = item.is_active ? 'is-active' : 'is-inactive';
                    const statusLabel = item.is_active ? 'Active' : 'Hidden';

                    return `
                        <article class="visual-highlight-card">
                            <img src="${escapeHtml(item.image_url || '')}" alt="Visual highlight image">
                            <div class="visual-highlight-card-body">
                                <div class="actions" style="justify-content: space-between;">
                                    <span class="visual-highlight-badge ${statusClass}">${escapeHtml(statusLabel)}</span>
                                    <div class="actions">
                                        <button type="button" class="secondary" data-action="edit" data-id="${item.id}">Edit</button>
                                        <button type="button" class="danger" data-action="delete" data-id="${item.id}">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    `;
                }).join('');

                setVisible(list, true, 'grid');
            }

            async function loadVisualHighlights(options = {}) {
                const preserveEditingState = Boolean(options.preserveEditingState);

                clearMessages();
                setVisible(loadingBox, true);
                setVisible(content, false);

                try {
                    const response = await fetch('/api/admin/visual-highlights', {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Could not load visual highlights.');
                    }

                    const payload = await response.json();
                    visualHighlights = Array.isArray(payload.data) ? payload.data : [];
                    renderList(visualHighlights);

                    if (preserveEditingState && editingId) {
                        const updatedItem = getEditingItem();
                        if (updatedItem) {
                            fillForm(updatedItem);
                        } else {
                            resetForm();
                        }
                    } else if (!editingId) {
                        resetForm();
                    }

                    setVisible(content, true);
                } catch (error) {
                    showError(error.message || 'Could not load visual highlights.');
                } finally {
                    setVisible(loadingBox, false);
                }
            }

            async function saveVisualHighlight(event) {
                event.preventDefault();
                clearMessages();
                clearFieldErrors();

                saveButton.disabled = true;
                saveButton.textContent = editingId ? 'Saving...' : 'Uploading...';

                try {
                    await ensureCsrfCookie();

                    const formData = new FormData();
                    const file = fileInput.files && fileInput.files[0];

                    if (file) {
                        formData.append('image', file);
                    }

                    formData.append('is_active', isActiveInput.checked ? '1' : '0');

                    let endpoint = '/api/admin/visual-highlights';

                    if (editingId) {
                        endpoint = `/api/admin/visual-highlights/${editingId}`;
                        formData.append('_method', 'PATCH');
                    }

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                        },
                        body: formData,
                    });

                    const responseBody = await response.json();

                    if (response.status === 422) {
                        Object.entries(responseBody.errors || {}).forEach(([field, messages]) => {
                            const errorElement = document.querySelector(`[data-visual-highlight-error-for="${field}"]`);
                            if (errorElement) {
                                errorElement.textContent = messages[0];
                            }
                        });

                        throw new Error('Please fix the highlighted fields.');
                    }

                    if (!response.ok) {
                        throw new Error(responseBody.message || 'Could not save the visual highlight.');
                    }

                    resetForm();
                    await loadVisualHighlights();
                    successBox.textContent = responseBody.message || 'Visual highlight saved successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    showError(error.message || 'Could not save the visual highlight.');
                } finally {
                    saveButton.disabled = false;
                    saveButton.textContent = editingId ? 'Save changes' : 'Upload image';
                }
            }

            async function deleteVisualHighlight(id) {
                const currentItem = visualHighlights.find((item) => item.id === id);
                const title = currentItem ? `Visual highlight #${id}` : `Visual highlight #${id}`;

                if (!window.confirm(`Delete "${title}"?`)) {
                    return;
                }

                clearMessages();

                try {
                    await ensureCsrfCookie();

                    const response = await fetch(`/api/admin/visual-highlights/${id}`, {
                        method: 'DELETE',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                        },
                    });

                    const responseBody = await response.json();

                    if (!response.ok) {
                        throw new Error(responseBody.message || 'Could not delete the visual highlight.');
                    }

                    if (editingId === id) {
                        resetForm();
                    }

                    await loadVisualHighlights();
                    successBox.textContent = responseBody.message || 'Visual highlight removed successfully.';
                    setVisible(successBox, true);
                } catch (error) {
                    showError(error.message || 'Could not delete the visual highlight.');
                }
            }

            refreshButton.addEventListener('click', () => {
                loadVisualHighlights({ preserveEditingState: true });
            });

            resetButton.addEventListener('click', () => {
                clearMessages();
                resetForm();
            });

            deleteButton.addEventListener('click', () => {
                if (editingId) {
                    deleteVisualHighlight(editingId);
                }
            });

            fileInput.addEventListener('change', syncPreviewFromCurrentState);

            form.addEventListener('submit', saveVisualHighlight);

            list.addEventListener('click', (event) => {
                const button = event.target.closest('[data-action]');

                if (!button) {
                    return;
                }

                const id = Number(button.dataset.id);

                if (!Number.isFinite(id) || id <= 0) {
                    return;
                }

                if (button.dataset.action === 'edit') {
                    const item = visualHighlights.find((entry) => entry.id === id);
                    if (item) {
                        clearMessages();
                        fillForm(item);
                    }
                }

                if (button.dataset.action === 'delete') {
                    deleteVisualHighlight(id);
                }
            });

            loadVisualHighlights();
        })();
    </script>
@endpush
