{{-- Shared add/edit form for staff, used by both create.blade.php and edit.blade.php --}}
@php
    $defaultPhotoUrl = asset('storage/staff-photos/'.\App\Http\Controllers\Hr\StaffController::DEFAULT_PHOTO);
    $photoUrl = isset($staff) && $staff->photo
        ? asset('storage/staff-photos/'.$staff->photo)
        : $defaultPhotoUrl;
@endphp
<div class="row">
    <div class="col-lg-12">
        <x-form.image-upload name="photo" id="profile-img-file-input"
                             :src="$photoUrl" :default-src="$defaultPhotoUrl" />
    </div>
    @isset($staff)
        <div class="col-lg-12">
            <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $staff->is_active))>
                <label class="form-check-label" for="is_active">Status</label>
            </div>
        </div>
    @endisset
</div>
<div class="row">
    <div class="col-lg-6">
        <div class="input-group">
            <label>Name<span>*</span></label>
            <input type="text" class="form-control" name="staff_name" maxlength="200" value="{{ old('staff_name', $staff->staff_name ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>IC No<span>*</span></label>
            <input type="text" class="form-control" name="ic" maxlength="30"
                   value="{{ old('ic', $staff->ic ?? '') }}" required
                   data-unique-check="{{ route('staff.check-unique') }}"
                   data-unique-check-message="This IC number is already registered."
                   @isset($staff) data-unique-check-ignore="{{ $staff->id }}" @endisset>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Gender<span>*</span></label>
            <select class="form-control" name="gender" required>
                <option value="" disabled @selected(old('gender', $staff->gender ?? '') === '')>Select Gender</option>
                <option value="Male" @selected(old('gender', $staff->gender ?? '') === 'Male')>MALE</option>
                <option value="Female" @selected(old('gender', $staff->gender ?? '') === 'Female')>FEMALE</option>
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Birth Date<span>*</span></label>
            <input type="date" class="form-control" name="dob" value="{{ old('dob', optional($staff->dob ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Contact No.<span>*</span></label>
            <input type="tel" class="form-control" name="contact" maxlength="30"
                   value="{{ old('contact', $staff->contact ?? '') }}" required
                   data-unique-check="{{ route('staff.check-unique') }}"
                   data-unique-check-message="This contact number is already registered."
                   @isset($staff) data-unique-check-ignore="{{ $staff->id }}" @endisset>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Email Address<span>*</span></label>
            <input type="email" class="form-control" name="email" maxlength="200"
                   value="{{ old('email', $staff->email ?? '') }}" required
                   data-unique-check="{{ route('staff.check-unique') }}"
                   data-unique-check-message="This email is already registered."
                   @isset($staff) data-unique-check-ignore="{{ $staff->id }}" @endisset>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Position<span>*</span></label>
            @php
                // On create, ?pid= preselects the position - that is how the
                // "Add member" button on the position list arrives here. On
                // edit the member's own position always wins.
                $selectedPosition = (int) old('position_id', $staff->position_id ?? request('pid'));
            @endphp
            <select class="form-control" name="position_id" required>
                <option value="" disabled @selected(! $selectedPosition)>Select Position</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->id }}" @selected($selectedPosition === $position->id)>
                        {{ $position->position_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Join Company Date<span>*</span></label>
            <input type="date" class="form-control" name="company_joined_date" value="{{ old('company_joined_date', optional($staff->company_joined_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label class="d-flex justify-content-between align-items-center">
                <span>Team<span>*</span></span>
                {{-- On a fresh install there are no teams yet, and leaving
                     this form to make one loses everything typed so far. --}}
                <a href="javascript:void(0);" class="quick-create-link"
                   data-quick-create-open="#quickCreateTeam">
                    <i class="ri-add-line"></i>New Team
                </a>
            </label>
            <select class="form-control" name="team_id" required>
                <option value="" disabled @selected(! old('team_id', $staff->team_id ?? null))>Select Team</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((int) old('team_id', $staff->team_id ?? null) === $team->id)>
                        {{ $team->team_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Join Team Date<span>*</span></label>
            <input type="date" class="form-control" name="team_joined_date" value="{{ old('team_joined_date', optional($staff->team_joined_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="input-group">
            <label>Address Details<span>*</span></label>
            <input type="text" class="form-control" name="address" maxlength="250" value="{{ old('address', $staff->address ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Postcode<span>*</span></label>
            <input type="text" class="form-control" name="postcode" maxlength="10" value="{{ old('postcode', $staff->postcode ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>City<span>*</span></label>
            <input type="text" class="form-control" name="city" maxlength="150" value="{{ old('city', $staff->city ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>States<span>*</span></label>
            <select class="form-control" name="states" required>
                <option value="" disabled @selected(old('states', $staff->states ?? '') === '')>Select States</option>
                @foreach (['Johor', 'Kedah', 'Kelantan', 'Malacca', 'Negeri Sembilan', 'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Labuan', 'Putrajaya'] as $state)
                    <option value="{{ $state }}" @selected(old('states', $staff->states ?? '') === $state)>{{ $state }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{--
    Setting a password by hand.

    Members normally set their own through the welcome email, and that stays
    the default - it is the only route that ends with nobody but the member
    knowing it. This is for when that does not work: a member who never got the
    email, or has lost access and needs to be let back in today.

    Always optional. Left blank on an edit, the existing password is untouched;
    left blank on a new member, the welcome email goes out as before.
--}}
<p id="form-sub-title" class="mt-4">Password</p>
<p id="footer-p" class="mb-3">
    @isset($staff)
        Leave blank to keep the member&rsquo;s current password. Setting one here
        replaces it immediately &mdash; tell them what it is, and ask them to change
        it from Settings once they are in.
    @else
        Leave blank and a welcome email will be sent so the member sets their own.
        Fill it in only if you need to hand them one directly.
    @endisset
</p>

<div class="row">
    <div class="col-lg-6">
        <div class="input-group">
            <label>{{ isset($staff) ? 'New Password' : 'Password' }}</label>
            <input type="password" class="form-control" name="password"
                   autocomplete="new-password" minlength="8" placeholder="At least 8 characters">
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Confirm Password</label>
            <input type="password" class="form-control" name="password_confirmation"
                   autocomplete="new-password" minlength="8">
        </div>
    </div>
</div>
