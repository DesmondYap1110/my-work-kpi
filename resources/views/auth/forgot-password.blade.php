@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <div id="form-title-div">
        <p id="form-title">Forgot Password</p>
        <p id="form-p">Enter your email and we'll send you a reset link.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert"><p class="alert-heading">{{ session('status') }}</p></div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><p class="alert-heading">{{ $errors->first() }}</p></div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="input-div">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div id="input-btn-div">
            <button type="submit" id="general-btn" class="btn1">Send Reset Link</button>
        </div>

        <div id="link-div">
            <p id="link-p">
                <a href="{{ route('login') }}">Back to Login</a>
            </p>
        </div>
    </form>
@endsection
