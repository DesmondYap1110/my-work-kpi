{{-- Shared add/edit form for staff, used by both create.blade.php and edit.blade.php --}}
@php
    $photoUrl = isset($staff) && $staff->staffimg
        ? asset('storage/staff-photos/'.$staff->staffimg)
        : asset('storage/staff-photos/default.jpg');
@endphp
<div class="row">
    <div class="col-lg-12">
        <x-form.image-upload name="photo" id="profile-img-file-input" :src="$photoUrl" />
    </div>
    @isset($staff)
        <div class="col-lg-12">
            <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                <input class="form-check-input" type="checkbox" role="switch" id="staffstatus" name="staffstatus" value="1" @checked(old('staffstatus', $staff->staffstatus))>
                <label class="form-check-label" for="staffstatus">Status</label>
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
                   @isset($staff) data-unique-check-ignore="{{ $staff->staff_id }}" @endisset>
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
                   @isset($staff) data-unique-check-ignore="{{ $staff->staff_id }}" @endisset>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Email Address<span>*</span></label>
            <input type="email" class="form-control" name="email" maxlength="200"
                   value="{{ old('email', $staff->email ?? '') }}" required
                   data-unique-check="{{ route('staff.check-unique') }}"
                   data-unique-check-message="This email is already registered."
                   @isset($staff) data-unique-check-ignore="{{ $staff->staff_id }}" @endisset>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Position<span>*</span></label>
            <select class="form-control" name="position_id" required>
                <option value="" disabled @selected(! old('position_id', $staff->position_id ?? null))>Select Position</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->position_ID }}" @selected((int) old('position_id', $staff->position_id ?? null) === $position->position_ID)>
                        {{ $position->position_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Join Company Date<span>*</span></label>
            <input type="date" class="form-control" name="datejoincompany" value="{{ old('datejoincompany', optional($staff->datejoincompany ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Team<span>*</span></label>
            <select class="form-control" name="team_id" required>
                <option value="" disabled @selected(! old('team_id', $staff->team_id ?? null))>Select Team</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->team_id }}" @selected((int) old('team_id', $staff->team_id ?? null) === $team->team_id)>
                        {{ $team->team_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Join Team Date<span>*</span></label>
            <input type="date" class="form-control" name="datejointeam" value="{{ old('datejointeam', optional($staff->datejointeam ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="input-group">
            <label>Address Details<span>*</span></label>
            <input type="text" class="form-control" name="staff_address" maxlength="250" value="{{ old('staff_address', $staff->staff_address ?? '') }}" required>
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
