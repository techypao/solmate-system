@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="card narrow">
        <h1 class="page-title">Login</h1>
        <p class="page-copy">Use your website account to access the Solmate admin pages.</p>

        <form method="POST" action="{{ route('login.attempt') }}" class="form-grid">
            @csrf

            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="password-field">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
                <button type="button" class="password-toggle" data-password-toggle data-target="password">
                    Show
                </button>
            </div>

            <div class="remember-row">
                <label class="checkbox-inline">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>
            </div>

            <div class="actions">
                <button type="submit">Login</button>
                <a class="button-link secondary" href="{{ route('register') }}">Create account</a>
                <a class="button-link secondary" href="{{ route('public.testimonies') }}">View testimonies</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach(button => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.target);

                if (!input) {
                    return;
                }

                const showingPassword = input.type === 'text';
                input.type = showingPassword ? 'password' : 'text';
                button.textContent = showingPassword ? 'Show' : 'Hide';
            });
        });
    </script>
@endpush
