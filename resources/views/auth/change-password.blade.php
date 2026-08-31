@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
    <div id="form-box" class="general-box" style="max-width: 480px;">
        <p id="form-sub-title">Change Password</p>

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <div class="input-group">
                <label>Current Password<span>*</span></label>
                <input type="password" class="form-control" name="current_password" required>
            </div>

            <div class="input-group">
                <label>New Password<span>*</span></label>
                <input type="password" class="form-control" name="password" minlength="8" required>
            </div>

            <div class="input-group">
                <label>Confirm New Password<span>*</span></label>
                <input type="password" class="form-control" name="password_confirmation" minlength="8" required>
            </div>

            <div id="form-btn-div">
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Update Password</button>
            </div>
        </form>
    </div>
@endsection
