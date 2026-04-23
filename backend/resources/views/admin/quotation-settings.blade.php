@extends('layouts.app', ['title' => 'Admin Quotation Settings'])

@section('content')
    <div class="admin-page-stack">
    <div class="card admin-hero-card">
        <p class="admin-page-eyebrow">Admin Quotations</p>
        <h1 class="page-title">Admin Quotation Settings</h1>
        <p class="page-copy">Edit numeric quotation defaults only. Formula logic stays fixed in the backend.</p>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Quotation Defaults</h2>
                <p class="admin-section-copy">These values feed the existing quotation workflow while the backend formula rules stay unchanged.</p>
            </div>
        </div>

        <div id="settings-loading" class="info-box">Loading current quotation settings...</div>
        <div id="settings-success" class="status" style="display: none;"></div>
        <div id="settings-error" class="error-box" style="display: none;"></div>

        <form id="settings-form" class="form-grid two-columns" style="display: none;">
            @foreach ($fields as $name => $meta)
                <div>
                    <label for="{{ $name }}">{{ $meta['label'] }}</label>
                    <input
                        id="{{ $name }}"
                        name="{{ $name }}"
                        type="number"
                        step="{{ $meta['step'] }}"
                        min="{{ $meta['min'] }}"
                        required
                    >
                    <div class="field-error" data-error-for="{{ $name }}"></div>
                </div>
            @endforeach

            <div class="actions" style="grid-column: 1 / -1;">
                <button id="save-button" type="submit">Save settings</button>
                <button id="reset-button" type="button" class="secondary">Reset to defaults</button>
                <span id="save-hint" class="muted">Changes are saved through the existing admin API.</span>
            </div>
        </form>
    </div>
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="__data_fieldNames">@json(array_keys($fields))</script>
    <script type="application/json" id="__data_defaults">@json($defaults)</script>
    <script>
        const form = document.getElementById('settings-form');
        const loadingBox = document.getElementById('settings-loading');
        const successBox = document.getElementById('settings-success');
        const errorBox = document.getElementById('settings-error');
        const saveButton = document.getElementById('save-button');
        const resetButton = document.getElementById('reset-button');
        const fieldNames = JSON.parse(document.getElementById('__data_fieldNames').textContent);
        const systemDefaults = JSON.parse(document.getElementById('__data_defaults').textContent);

        function setVisible(element, visible) {
            element.style.display = visible ? 'block' : 'none';
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

        function clearMessages() {
            successBox.textContent = '';
            errorBox.textContent = '';
            setVisible(successBox, false);
            setVisible(errorBox, false);
        }

        function clearFieldErrors() {
            document.querySelectorAll('[data-error-for]').forEach((element) => {
                element.textContent = '';
            });
        }

        function showTopError(message) {
            errorBox.textContent = message;
            setVisible(errorBox, true);
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

        function populateForm(data) {
            fieldNames.forEach((field) => {
                const input = form.elements.namedItem(field);

                if (input && Object.prototype.hasOwnProperty.call(data, field)) {
                    input.value = data[field];
                }
            });
        }

        async function loadSettings() {
            clearMessages();
            clearFieldErrors();
            setVisible(loadingBox, true);
            setVisible(form, false);

            try {
                const response = await fetch('/api/admin/quotation-settings', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Could not load quotation settings.');
                }

                const payload = await response.json();
                populateForm(payload.data ?? {});
                setVisible(form, true);
            } catch (error) {
                showTopError(error.message || 'Could not load quotation settings.');
            } finally {
                setVisible(loadingBox, false);
            }
        }

        async function saveSettings(event) {
            event.preventDefault();
            clearMessages();
            clearFieldErrors();

            saveButton.disabled = true;
            saveButton.textContent = 'Saving...';

            try {
                await ensureCsrfCookie();

                const payload = {};

                fieldNames.forEach((field) => {
                    payload[field] = Number(form.elements.namedItem(field).value);
                });

                const response = await fetch('/api/admin/quotation-settings', {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                    },
                    body: JSON.stringify(payload),
                });

                const responseBody = await response.json();

                if (response.status === 422) {
                    const errors = responseBody.errors || {};
                    Object.entries(errors).forEach(([field, messages]) => {
                        const errorElement = document.querySelector(`[data-error-for="${field}"]`);
                        if (errorElement) {
                            errorElement.textContent = messages[0];
                        }
                    });

                    throw new Error('Please fix the highlighted fields.');
                }

                if (!response.ok) {
                    throw new Error(responseBody.message || 'Could not update quotation settings.');
                }

                populateForm(responseBody.data ?? {});
                successBox.textContent = responseBody.message || 'Quotation settings updated successfully.';
                setVisible(successBox, true);
            } catch (error) {
                showTopError(error.message || 'Could not update quotation settings.');
            } finally {
                saveButton.disabled = false;
                saveButton.textContent = 'Save settings';
            }
        }

        form.addEventListener('submit', saveSettings);
        resetButton.addEventListener('click', () => {
            clearMessages();
            clearFieldErrors();
            populateForm(systemDefaults);
            successBox.textContent = 'Form pre-filled with system defaults. Click "Save settings" to apply.';
            setVisible(successBox, true);
        });
        loadSettings();
    </script>
@endpush
