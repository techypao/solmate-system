@extends('layouts.app', ['title' => 'Admin Profile'])

@section('content')
    @php
        $adminProfilePictureUrl = $user->profile_picture ? asset('storage/' . ltrim($user->profile_picture, '/')) : null;
    @endphp
    <style>
        .admin-profile-picture-card {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 22px;
            align-items: center;
        }

        .admin-profile-picture-preview {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 3px solid #d4a017;
            background: #102a43;
            color: #ffffff;
            font-size: 32px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .admin-profile-picture-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .admin-profile-picture-body {
            display: grid;
            gap: 12px;
        }

        .admin-profile-picture-copy {
            margin: 0;
            color: #52606d;
            font-size: 14px;
            line-height: 1.7;
        }

        .admin-profile-picture-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .admin-profile-picture-feedback {
            display: none;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 14px;
        }

        .admin-profile-picture-feedback.show {
            display: block;
        }

        .admin-profile-picture-feedback.success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .admin-profile-picture-feedback.error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .admin-profile-picture-helper {
            margin: 0;
            font-size: 12px;
            color: #64748b;
        }

        @media (max-width: 700px) {
            .admin-profile-picture-card {
                grid-template-columns: 1fr;
                justify-items: flex-start;
            }
        }
    </style>
    <div
        class="admin-page-stack"
        data-user-name="{{ $user->name }}"
        data-profile-picture-path="{{ $user->profile_picture ?? '' }}"
    >
    <div class="card admin-hero-card">
        <div class="section-header">
            <div>
                <p class="admin-page-eyebrow">Admin Account</p>
                <h1 class="page-title">Admin Profile</h1>
                <p class="page-copy">Manage your admin account details and keep your website login credentials up to date.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.quotation-settings') }}">Back to Admin Tools</a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Account name</div>
                <div class="summary-value" style="font-size: 22px; line-height: 1.2;">{{ $user->name }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Email</div>
                <div class="muted" style="font-size: 16px; color: #102a43;">{{ $user->email }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Role</div>
                <div class="muted" style="font-size: 16px; color: #102a43;">{{ \Illuminate\Support\Str::headline($user->role) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Session</div>
                <div class="muted" style="font-size: 16px; color: #102a43;">Signed in</div>
            </div>
        </div>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Profile picture</h2>
                <p class="page-copy" style="margin-bottom: 0;">Upload a JPG, JPEG, PNG, or WEBP image up to 2 MB for your admin account.</p>
            </div>
        </div>

        <div class="admin-profile-picture-card">
            <div class="admin-profile-picture-preview" id="admin-profile-picture-preview">
                @if ($adminProfilePictureUrl)
                    <img
                        src="{{ $adminProfilePictureUrl }}"
                        alt="{{ $user->name }} profile picture"
                        id="admin-profile-picture-image"
                    >
                @else
                    <span id="admin-profile-picture-fallback">?</span>
                @endif
            </div>
            <div class="admin-profile-picture-body">
                <div id="admin-profile-picture-msg" class="admin-profile-picture-feedback"></div>
                <p class="admin-profile-picture-copy">The top-right profile button and this page preview will update as soon as the upload succeeds.</p>
                <div>
                    <label for="admin-profile-picture-input">Choose image</label>
                    <input id="admin-profile-picture-input" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <div class="field-error" id="admin-profile-picture-error"></div>
                    <p class="admin-profile-picture-helper" id="admin-profile-picture-helper">No file selected.</p>
                </div>
                <div class="admin-profile-picture-actions">
                    <button type="button" id="admin-profile-picture-upload-btn">Upload picture</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Update account information</h2>
                <p class="page-copy" style="margin-bottom: 0;">Edit your admin name and email. Changes apply to your own website account only.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.update') }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-grid two-columns">
                <div>
                    <label for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                    <div class="field-error">@error('name') {{ $message }} @enderror</div>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Save profile</button>
            </div>
        </form>
    </div>

    <div class="card admin-section-surface">
        <div class="section-header">
            <div>
                <h2 class="admin-section-title">Change password</h2>
                <p class="page-copy" style="margin-bottom: 0;">Enter your current password before setting a new one for your admin web account.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.profile.password.update') }}" class="form-grid">
            @csrf
            @method('PUT')

            <div class="form-grid two-columns">
                <div>
                    <label for="current_password">Current password</label>
                    <input id="current_password" type="password" name="current_password" required>
                    <div class="field-error">@error('current_password') {{ $message }} @enderror</div>
                </div>

                <div>
                    <label for="new_password">New password</label>
                    <input id="new_password" type="password" name="new_password" required>
                    <div class="field-error">@error('new_password') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="new_password_confirmation">Confirm new password</label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation" required>
                    <div class="field-error"></div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Update password</button>
            </div>
        </form>
    </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var adminPage = document.querySelector('.admin-page-stack');
    var adminUserName = adminPage ? adminPage.dataset.userName || '' : '';
    var initialProfilePicturePath = adminPage ? adminPage.dataset.profilePicturePath || '' : '';
    var input = document.getElementById('admin-profile-picture-input');
    var uploadBtn = document.getElementById('admin-profile-picture-upload-btn');
    var helper = document.getElementById('admin-profile-picture-helper');
    var errorEl = document.getElementById('admin-profile-picture-error');
    var msgEl = document.getElementById('admin-profile-picture-msg');
    var preview = document.getElementById('admin-profile-picture-preview');
    var previewImage = document.getElementById('admin-profile-picture-image');
    var previewFallback = document.getElementById('admin-profile-picture-fallback');

    if (!input || !uploadBtn) {
        return;
    }

    function initials(name) {
        return String(name || '').trim().split(/\s+/).slice(0, 2).map(function (word) {
            return word[0] || '';
        }).join('').toUpperCase() || '?';
    }

    function getCookie(name) {
        var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.*+?^=!:${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)'));
        return match ? decodeURIComponent(match[1]) : null;
    }

    async function ensureCsrf() {
        if (!getCookie('XSRF-TOKEN')) {
            await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
        }
    }

    function showMessage(type, text) {
        msgEl.className = 'admin-profile-picture-feedback show ' + type;
        msgEl.textContent = text;
    }

    function clearMessage() {
        msgEl.className = 'admin-profile-picture-feedback';
        msgEl.textContent = '';
    }

    function clearError() {
        errorEl.textContent = '';
    }

    function setPreview(path) {
        var url = path ? '/storage/' + String(path).replace(/^\/+/, '') : '';

        if (url) {
            if (!previewImage) {
                previewImage = document.createElement('img');
                previewImage.id = 'admin-profile-picture-image';
                preview.insertBefore(previewImage, previewFallback);
            }

            previewImage.src = url;
            previewImage.alt = adminUserName + ' profile picture';
            previewImage.style.display = '';

            if (previewFallback) {
                previewFallback.style.display = 'none';
            }
        } else if (previewFallback) {
            previewFallback.textContent = initials(adminUserName);
            previewFallback.style.display = '';
            if (previewImage) {
                previewImage.style.display = 'none';
            }
        }

        document.querySelectorAll('[data-profile-menu-button]').forEach(function (button) {
            button.classList.toggle('has-image', !!url);
        });

        document.querySelectorAll('[data-profile-menu-image]').forEach(function (imageEl) {
            imageEl.style.display = url ? '' : 'none';
            if (url) {
                imageEl.src = url;
                imageEl.alt = adminUserName + ' profile picture';
            }
        });

        document.querySelectorAll('[data-profile-menu-icon]').forEach(function (iconEl) {
            iconEl.style.display = url ? 'none' : '';
        });
    }

    input.addEventListener('change', function () {
        clearError();
        clearMessage();
        helper.textContent = input.files && input.files[0] ? input.files[0].name : 'No file selected.';
    });

    uploadBtn.addEventListener('click', async function () {
        clearError();
        clearMessage();

        if (!input.files || !input.files[0]) {
            errorEl.textContent = 'Please choose an image file to upload.';
            showMessage('error', 'Please choose an image file to upload.');
            return;
        }

        var formData = new FormData();
        formData.append('profile_picture', input.files[0]);

        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading...';

        try {
            await ensureCsrf();

            var response = await fetch('/api/user/profile-picture', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') || '',
                },
                body: formData,
            });

            var payload = await response.json().catch(function () { return {}; });

            if (!response.ok) {
                if (payload.errors && payload.errors.profile_picture) {
                    errorEl.textContent = payload.errors.profile_picture[0];
                }
                throw new Error(payload.message || 'Could not upload profile picture.');
            }

            setPreview(payload.user && payload.user.profile_picture ? payload.user.profile_picture : null);
            input.value = '';
            helper.textContent = 'No file selected.';
            showMessage('success', payload.message || 'Profile picture updated successfully.');
        } catch (error) {
            showMessage('error', error.message || 'Could not upload profile picture.');
        } finally {
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'Upload picture';
        }
    });

    setPreview(initialProfilePicturePath);
})();
</script>
@endpush
