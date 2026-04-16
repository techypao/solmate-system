@extends('layouts.app', ['title' => 'Register Technician'])

@section('content')
    <div class="card">
        <div class="section-header">
            <div>
                <h1 class="page-title">Register Technician</h1>
                <p class="page-copy">Create technician accounts directly from the admin website. New accounts created here are saved with the <strong>technician</strong> role and can be used in your existing assignment flows.</p>
            </div>
            <a class="button-link secondary" href="{{ route('admin.request-assignments') }}">Open Request Assignments</a>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Existing technicians</div>
                <div class="summary-value">{{ $technicians->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Account access</div>
                <div class="muted">Admin-only page</div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Technician Account Details</h2>

        <form method="POST" action="{{ route('admin.technicians.store') }}" class="form-grid">
            @csrf

            <div class="form-grid two-columns">
                <div>
                    <label for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                    <div class="field-error">@error('name') {{ $message }} @enderror</div>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                    <div class="field-error">@error('email') {{ $message }} @enderror</div>
                </div>
            </div>

            <div class="form-grid two-columns">
                <div>
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                    <div class="field-error">@error('password') {{ $message }} @enderror</div>
                </div>

                <div>
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                    <div class="field-error"></div>
                </div>
            </div>

            <div class="actions">
                <button type="submit">Create technician account</button>
                <a class="button-link secondary" href="{{ route('admin.request-assignments') }}">Back to Assignments</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="section-header">
            <div>
                <h2 style="margin: 0;">Existing Technicians</h2>
                <p class="page-copy" style="margin-bottom: 0;">Use this list to confirm which technician accounts are already available for request assignment.</p>
            </div>
        </div>

        @if ($technicians->isEmpty())
            <div class="info-box" style="margin-bottom: 0;">No technician accounts have been created yet.</div>
        @else
            <div class="stack">
                @foreach ($technicians as $technician)
                    <div class="list-row">
                        <div>
                            <strong>{{ $technician->name }}</strong>
                            <div class="muted">{{ $technician->email }}</div>
                        </div>
                        <span class="badge badge-neutral">Technician</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
