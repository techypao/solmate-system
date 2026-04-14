@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="card">
        <h1 class="page-title">Website Dashboard</h1>
        <p class="page-copy">This simple website layer is ready for authentication and admin settings management.</p>

        <div class="stack">
            <div class="info-box">
                <strong>Current role:</strong> {{ $user->role }}
            </div>

            @if ($user->role === \App\Models\User::ROLE_ADMIN)
                <div>
                    <a class="button-link" href="{{ route('admin.quotation-settings') }}">Open Admin Quotation Settings</a>
                </div>
            @else
                <div class="info-box">
                    Your account can log in to the website, but the quotation settings page is available to admin users only.
                </div>
            @endif
        </div>
    </div>
@endsection
