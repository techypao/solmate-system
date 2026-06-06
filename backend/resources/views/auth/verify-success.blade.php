@extends('layouts.app', ['title' => 'Email Verified'])

@section('content')
    <style>
        .verify-shell {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: clamp(560px, calc(100vh - 160px), 760px);
            padding: 36px 0 60px;
        }

        .verify-card {
            width: min(100%, 620px);
            padding: clamp(28px, 5vw, 46px);
            border: 1px solid #DDE7EE;
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(248, 251, 255, 1)),
                #ffffff;
            box-shadow: 0 28px 70px rgba(15, 23, 42, .12);
            text-align: center;
        }

        .verify-logo {
            display: block;
            width: auto;
            height: 54px;
            margin: 0 auto 28px;
        }

        .verify-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px;
            height: 72px;
            margin-bottom: 24px;
            border-radius: 999px;
            background: #EAF9FD;
            color: #20A7C9;
            font-size: 34px;
            box-shadow: inset 0 0 0 1px rgba(32, 167, 201, .18);
        }

        .verify-title {
            margin: 0 0 14px;
            color: #123A5A;
            font-size: clamp(2rem, 5vw, 2.6rem);
            line-height: 1.08;
            font-weight: 800;
        }

        .verify-message {
            max-width: 460px;
            margin: 0 auto 30px;
            color: #5F7387;
            font-size: 16px;
            line-height: 1.7;
        }

        .verify-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 28px;
            border-radius: 999px;
            background: #123A5A;
            color: #ffffff;
            font-size: 15px;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 14px 30px rgba(18, 58, 90, .18);
            transition: background-color .2s ease, transform .2s ease;
        }

        .verify-button:hover {
            background: #0F2F4A;
            text-decoration: none;
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .verify-shell {
                align-items: flex-start;
                padding-top: 28px;
            }

            .verify-card {
                border-radius: 22px;
            }
        }
    </style>

    <section class="verify-shell" aria-labelledby="verify-title">
        <div class="verify-card">
            <img src="{{ asset('images/rdy-logo-transparent.png') }}" alt="RDY logo" class="verify-logo">

            <div class="verify-icon" aria-hidden="true">✓</div>

            <h1 id="verify-title" class="verify-title">✅ Email Verified Successfully</h1>

            <p class="verify-message">
                @if (! empty($already))
                    Your email is already verified.
                @else
                    Your account has been successfully verified. You can now log in.
                @endif
            </p>

            <a href="https://solmatebyrdy.com/login" class="verify-button">Go to Login</a>
        </div>
    </section>
@endsection
