@extends('layouts.app', ['title' => 'Admin Quotation Settings'])

@section('content')
    @php
        $fieldGroups = [
            [
                'title' => 'Energy Assumptions',
                'copy' => 'Baseline values used when estimating household consumption and production.',
                'fields' => ['rate_per_kwh', 'days_in_month', 'sun_hours'],
            ],
            [
                'title' => 'System Sizing',
                'copy' => 'Factors that influence recommended panels, battery requirement, and default panel capacity.',
                'fields' => ['pv_safety_factor', 'battery_factor', 'battery_voltage', 'default_panel_watts'],
            ],
            [
                'title' => 'Pricing Defaults',
                'copy' => 'Fallback values used by pre-inspection estimates and technician quotation setup.',
                'fields' => ['labor_percentage', 'default_bos_cost', 'default_misc_cost', 'initial_price_per_kw', 'net_metering_price'],
            ],
        ];

        $summaryFields = [
            'rate_per_kwh' => ['label' => 'Rate / kWh', 'prefix' => 'PHP '],
            'default_panel_watts' => ['label' => 'Panel watts', 'suffix' => ' W'],
            'initial_price_per_kw' => ['label' => 'Estimate / kW', 'prefix' => 'PHP '],
            'net_metering_price' => ['label' => 'Net metering', 'prefix' => 'PHP '],
        ];
    @endphp

    <style>
        .quotation-settings-hero {
            display: grid;
            gap: 18px;
        }

        .quotation-settings-hero-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
            flex-wrap: wrap;
        }

        .quotation-settings-hero-copy {
            max-width: 720px;
        }

        .quotation-settings-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .quotation-settings-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .quotation-settings-summary-item {
            min-height: 96px;
            padding: 15px 16px;
            border: 1px solid rgba(203, 219, 229, 0.8);
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.76);
        }

        .quotation-settings-summary-label {
            margin-bottom: 8px;
            color: #5E7288;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .quotation-settings-summary-value {
            color: #123A5A;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.1;
            word-break: break-word;
        }

        .quotation-settings-status-row {
            display: grid;
            gap: 12px;
        }

        .quotation-settings-form {
            display: grid;
            gap: 18px;
        }

        .quotation-settings-group {
            padding: 18px;
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            background: #ffffff;
        }

        .quotation-settings-group-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }

        .quotation-settings-group-title {
            margin: 0;
            color: #123A5A;
            font-size: 16px;
            font-weight: 800;
            line-height: 1.3;
        }

        .quotation-settings-group-copy {
            margin: 5px 0 0;
            color: #5E7288;
            font-size: 13px;
            line-height: 1.6;
        }

        .quotation-settings-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 28px;
            padding: 0 10px;
            border-radius: 999px;
            background: #EAF9FD;
            color: #20A7C9;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
        }

        .quotation-settings-field-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .quotation-settings-field {
            display: grid;
            align-content: start;
            min-height: 104px;
        }

        .quotation-settings-field input {
            min-height: 46px;
        }

        .quotation-settings-actions {
            position: sticky;
            bottom: 0;
            z-index: 4;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 4px;
            padding: 16px;
            border: 1px solid #DDE7EE;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 -8px 24px rgba(18, 58, 90, 0.08);
            backdrop-filter: blur(10px);
        }

        .quotation-settings-action-copy {
            min-width: min(100%, 280px);
            color: #5E7288;
            font-size: 13px;
            line-height: 1.5;
        }

        .quotation-settings-action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 920px) {
            .quotation-settings-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .quotation-settings-summary {
                grid-template-columns: 1fr;
            }

            .quotation-settings-actions,
            .quotation-settings-action-buttons,
            .quotation-settings-action-buttons button,
            .quotation-settings-links,
            .quotation-settings-links .button-link {
                width: 100%;
            }

            .quotation-settings-action-buttons button,
            .quotation-settings-links .button-link {
                min-height: 44px;
            }
        }
    </style>

    <div class="admin-page-stack">
        <div class="card admin-hero-card quotation-settings-hero">
            <div class="quotation-settings-hero-row">
                <div class="quotation-settings-hero-copy">
                    <p class="admin-page-eyebrow">Admin Quotations</p>
                    <h1 class="page-title">Admin Quotation Settings</h1>
                    <p class="page-copy">Tune the numeric defaults used by pre-inspection estimates and inspection-based quotation setup. Formula logic stays fixed in the backend.</p>
                </div>

                <div class="quotation-settings-links">
                    <a class="button-link secondary" href="{{ route('quotations.item-builder') }}">Open Item Builder</a>
                    <a class="button-link secondary" href="{{ route('admin.pricing-catalog') }}">Pricing Management</a>
                </div>
            </div>

            <div class="quotation-settings-summary" aria-label="Current quotation defaults summary">
                @foreach ($summaryFields as $field => $summary)
                    <div class="quotation-settings-summary-item">
                        <div class="quotation-settings-summary-label">{{ $summary['label'] }}</div>
                        <div
                            class="quotation-settings-summary-value"
                            data-summary-field="{{ $field }}"
                            data-summary-prefix="{{ $summary['prefix'] ?? '' }}"
                            data-summary-suffix="{{ $summary['suffix'] ?? '' }}"
                        >
                            -
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card admin-section-surface">
            <div class="section-header">
                <div>
                    <h2 class="admin-section-title">Quotation Defaults</h2>
                    <p class="admin-section-copy">Review the current values, adjust only what changed, then save once. Reset only pre-fills the form until you confirm with Save.</p>
                </div>
            </div>

            <div class="quotation-settings-status-row">
                <div id="settings-loading" class="info-box">Loading current quotation settings...</div>
                <div id="settings-success" class="status" style="display: none;"></div>
                <div id="settings-error" class="error-box" style="display: none;"></div>
            </div>

            <form id="settings-form" class="quotation-settings-form" style="display: none;">
                @foreach ($fieldGroups as $group)
                    <section class="quotation-settings-group" aria-labelledby="quotation-settings-group-{{ \Illuminate\Support\Str::slug($group['title']) }}">
                        <div class="quotation-settings-group-head">
                            <div>
                                <h3 id="quotation-settings-group-{{ \Illuminate\Support\Str::slug($group['title']) }}" class="quotation-settings-group-title">{{ $group['title'] }}</h3>
                                <p class="quotation-settings-group-copy">{{ $group['copy'] }}</p>
                            </div>
                            <span class="quotation-settings-count">{{ count($group['fields']) }}</span>
                        </div>

                        <div class="quotation-settings-field-grid">
                            @foreach ($group['fields'] as $name)
                                @continue (! isset($fields[$name]))
                                @php($meta = $fields[$name])
                                <div class="quotation-settings-field">
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
                        </div>
                    </section>
                @endforeach

                <div class="quotation-settings-actions">
                    <span id="save-hint" class="quotation-settings-action-copy">Changes are saved through the existing admin API.</span>
                    <div class="quotation-settings-action-buttons">
                        <button id="reset-button" type="button" class="secondary">Reset to defaults</button>
                        <button id="save-button" type="submit">Save settings</button>
                    </div>
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
        const saveHint = document.getElementById('save-hint');
        const fieldNames = JSON.parse(document.getElementById('__data_fieldNames').textContent);
        const systemDefaults = JSON.parse(document.getElementById('__data_defaults').textContent);
        let lastSavedSettings = {};

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

        function formatSettingValue(value) {
            const number = Number(value);

            if (!Number.isFinite(number)) {
                return '-';
            }

            return number.toLocaleString('en-US', {
                minimumFractionDigits: Number.isInteger(number) ? 0 : 2,
                maximumFractionDigits: 2,
            });
        }

        function updateSummary(data) {
            document.querySelectorAll('[data-summary-field]').forEach((element) => {
                const field = element.dataset.summaryField;
                const prefix = element.dataset.summaryPrefix || '';
                const suffix = element.dataset.summarySuffix || '';

                element.textContent = `${prefix}${formatSettingValue(data[field])}${suffix}`;
            });
        }

        function currentFormData() {
            const payload = {};

            fieldNames.forEach((field) => {
                payload[field] = Number(form.elements.namedItem(field).value);
            });

            return payload;
        }

        function updateDirtyState() {
            if (!form || !saveHint) {
                return;
            }

            const current = currentFormData();
            const changedFields = fieldNames.filter((field) => {
                const currentValue = Number(current[field]);
                const savedValue = Number(lastSavedSettings[field]);

                return Number.isFinite(currentValue)
                    && Number.isFinite(savedValue)
                    && currentValue !== savedValue;
            });

            saveHint.textContent = changedFields.length
                ? `${changedFields.length} unsaved ${changedFields.length === 1 ? 'change' : 'changes'} ready to save.`
                : 'Settings match the last saved values.';
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

            updateSummary(data);
            updateDirtyState();
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
                lastSavedSettings = payload.data ?? {};
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

                const payload = currentFormData();

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

                lastSavedSettings = responseBody.data ?? {};
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
        form.addEventListener('input', updateDirtyState);
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
