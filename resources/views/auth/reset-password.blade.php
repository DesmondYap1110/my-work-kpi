@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <div id="form-title-div">
        <p id="form-title">Reset Password</p>
        <p id="form-p">Choose a new password for your account.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><p class="alert-heading">{{ $errors->first() }}</p></div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="input-div">
            <label>Email</label>
            <input type="email" class="form-control" name="email" value="{{ old('email', $email) }}" required autofocus>
        </div>

        <div class="input-div">
            <label>New Password</label>
            <input type="password" class="form-control" name="password" minlength="8" required>
        </div>

        <div class="input-div">
            <label>Confirm New Password</label>
            <input type="password" class="form-control" name="password_confirmation" minlength="8" required>
        </div>

        <div id="input-btn-div">
            <button type="submit" id="general-btn" class="btn1">Reset Password</button>
        </div>
    </form>
@endsection
