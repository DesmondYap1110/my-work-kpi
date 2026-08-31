{{-- Shared add/edit form fields for project phases --}}
<div class="row">
    <div class="col-lg-12">
        <div class="input-group">
            <label>Project</label>
            <input type="text" class="form-control" value="{{ $project->p_Title }}" readonly>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Title<span>*</span></label>
            <input type="text" class="form-control" name="p_PTitle" maxlength="200" value="{{ old('p_PTitle', $phase->p_PTitle ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Type<span>*</span></label>
            <select class="form-control" name="p_Type" required>
                <option value="" disabled @selected(is_null(old('p_Type', $phase->p_Type->value ?? null)))>Select Type</option>
                @foreach (\App\Enums\PhaseType::cases() as $type)
                    <option value="{{ $type->value }}" @selected((int) old('p_Type', $phase->p_Type->value ?? null) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Start Date<span>*</span></label>
            <input type="date" class="form-control" name="p_SDate" min="{{ $project->p_SDate->format('Y-m-d') }}" max="{{ $project->p_EDate->format('Y-m-d') }}" value="{{ old('p_SDate', optional($phase->p_SDate ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Due Date<span>*</span></label>
            <input type="date" class="form-control" name="p_DDate" min="{{ $project->p_SDate->format('Y-m-d') }}" max="{{ $project->p_EDate->format('Y-m-d') }}" value="{{ old('p_DDate', optional($phase->p_DDate ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="input-group">
            <label>Project Phase Remark</label>
            <input type="file" class="form-control" name="p_Remark">
            @isset($phase)
                @if ($phase->p_Remark)
                    <span id="note-p" class="d-block">Current: {{ $phase->p_Remark }}</span>
                @endif
            @endisset
        </div>
    </div>
    <div class="col-lg-12">
        <div class="input-group">
            <label>Invoice</label>
            <input type="file" class="form-control" name="p_Invoice">
            @isset($phase)
                @if ($phase->p_Invoice)
                    <span id="note-p" class="d-block">Current: {{ $phase->p_Invoice }}</span>
                @endif
            @endisset
        </div>
    </div>
</div>
