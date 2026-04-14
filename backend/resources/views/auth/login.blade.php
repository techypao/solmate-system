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

            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div>
                <label>
                    <input type="checkbox" name="remember" value="1" style="width: auto; margin-right: 8px;">
                    Remember me
                </label>
            </div>

            <div class="actions">
                <button type="submit">Login</button>
                <a class="button-link secondary" href="{{ route('register') }}">Create account</a>
            </div>
        </form>
    </div>
@endsection
