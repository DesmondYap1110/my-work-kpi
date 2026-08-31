@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div id="form-title-div">
        <p id="form-title">Welcome Back</p>
        <p id="form-p">Login to manage your account.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert"><p class="alert-heading">{{ session('status') }}</p></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><p class="alert-heading">{{ $errors->first() }}</p></div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf

        <div class="input-div">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="input-div">
            <label>Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <div id="check-div" class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Stay Login</label>
        </div>

        <div id="input-btn-div">
            <button type="submit" id="general-btn" class="btn1">Login</button>
        </div>

        <div id="link-div">
            <p id="link-p">
                <a href="{{ route('password.request') }}">Forgot Password?</a>
            </p>
        </div>
    </form>
@endsection
