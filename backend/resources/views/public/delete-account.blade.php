@extends('layouts.app', ['title' => 'Delete Account · SolMate'])

@section('content')
<style>
    .delete-account-page {
        max-width: 760px;
        margin: 0 auto;
        padding: 8px 0 48px;
    }

    .delete-account-hero {
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
    }

    .delete-account-eyebrow {
        margin: 0;
        color: #dc2626;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .delete-account-title {
        margin: 0;
        color: #123A5A;
        font-size: 32px;
        line-height: 1.15;
        font-weight: 900;
    }

    .delete-account-copy {
        margin: 0;
        color: #5E7288;
        font-size: 15px;
        line-height: 1.7;
    }

    .delete-account-warning {
        margin: 20px 0;
        padding: 16px 18px;
        border: 1px solid #fecaca;
        border-radius: 12px;
        background: #fff1f2;
        color: #991b1b;
        font-size: 14px;
        line-height: 1.65;
    }

    .delete-account-list {
        margin: 10px 0 0;
        padding-left: 20px;
    }

    .delete-account-list li + li {
        margin-top: 6px;
    }

    .delete-account-card {
        background: #ffffff;
        border: 1px solid #DDE7EE;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 10px 24px rgba(18, 58, 90, 0.06);
    }

    .delete-account-form {
        display: grid;
        gap: 16px;
    }

    .delete-account-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .delete-account-submit {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #ffffff;
        box-shadow: 0 10px 24px rgba(220, 38, 38, 0.16);
    }

    @media (max-width: 640px) {
        .delete-account-title {
            font-size: 26px;
        }

        .delete-account-card {
            padding: 20px;
        }

        .delete-account-actions button {
            width: 100%;
        }
    }
</style>

<div class="delete-account-page">
    <div class="delete-account-hero">
        <p class="delete-account-eyebrow">Account Deletion</p>
        <h1 class="delete-account-title">Request deletion of your SolMate account</h1>
        <p class="delete-account-copy">
            Use this page to submit a request to permanently delete your SolMate account and associated personal data.
        </p>
    </div>

    <div class="delete-account-warning">
        <strong>Important:</strong> Account deletion is permanent. It may remove your access to quotations, service history, appointments, inspections, testimonies, profile details, and other account-related information.
        <ul class="delete-account-list">
            <li>You may lose access to active or past SolMate service records.</li>
            <li>Some information may be retained only when required for legal, security, or operational recordkeeping.</li>
            <li>Submitting this form sends your request to the admin team for review and processing.</li>
        </ul>
    </div>

    <div class="delete-account-card">
        <form class="delete-account-form" method="POST" action="{{ route('delete-account.store') }}">
            @csrf

            <div>
                <label for="email">SolMate account email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $customerEmail) }}"
                    placeholder="you@example.com"
                    autocomplete="email"
                    required
                >
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="reason">Reason for account deletion (optional)</label>
                <textarea
                    id="reason"
                    name="reason"
                    placeholder="You may share why you want your account deleted."
                >{{ old('reason') }}</textarea>
                @error('reason')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="delete-account-actions">
                <button type="submit" class="delete-account-submit">Submit Account Deletion Request</button>
                <a class="button-link secondary" href="{{ route('home') }}">Back to SolMate</a>
            </div>
        </form>
    </div>
</div>
@endsection
