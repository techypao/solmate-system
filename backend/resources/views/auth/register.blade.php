@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <div class="card narrow">
        <h1 class="page-title">Register</h1>
        <p class="page-copy">Create a basic website account. New registrations are saved as <strong>customer</strong> users by default.</p>

        <form method="POST" action="{{ route('register.store') }}" class="form-grid">
            @csrf

            <div>
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <div>
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>

            <div class="actions">
                <button type="submit">Register</button>
                <a class="button-link secondary" href="{{ route('login') }}">Back to login</a>
            </div>
        </form>
    </div>
@endsection
