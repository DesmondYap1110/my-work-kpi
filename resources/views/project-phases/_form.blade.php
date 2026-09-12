{{-- Shared add/edit form fields for project phases --}}
<div class="row">
    <div class="col-lg-12">
        <div class="input-group">
            <label>Project</label>
            <input type="text" class="form-control" value="{{ $project->title }}" readonly>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Title<span>*</span></label>
            <input type="text" class="form-control" name="title" maxlength="200" value="{{ old('title', $phase->title ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Type<span>*</span></label>
            <select class="form-control" name="type" required>
                <option value="" disabled @selected(is_null(old('type', $phase->type->value ?? null)))>Select Type</option>
                @foreach (\App\Enums\PhaseType::cases() as $type)
                    <option value="{{ $type->value }}" @selected((int) old('type', $phase->type->value ?? null) === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Start Date<span>*</span></label>
            <input type="date" class="form-control" name="start_date" min="{{ $project->start_date->format('Y-m-d') }}" max="{{ $project->end_date->format('Y-m-d') }}" value="{{ old('start_date', optional($phase->start_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Due Date<span>*</span></label>
            <input type="date" class="form-control" name="due_date" min="{{ $project->start_date->format('Y-m-d') }}" max="{{ $project->end_date->format('Y-m-d') }}" value="{{ old('due_date', optional($phase->due_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="input-group">
            <label>Project Phase Remark</label>
            <input type="file" class="form-control" name="remark_file">
            @isset($phase)
                @if ($phase->remark_file)
                    <span id="note-p" class="d-block">Current: {{ $phase->remark_file }}</span>
                @endif
            @endisset
        </div>
    </div>
    <div class="col-lg-12">
        <div class="input-group">
            <label>Invoice</label>
            <input type="file" class="form-control" name="invoice_file">
            @isset($phase)
                @if ($phase->invoice_file)
                    <span id="note-p" class="d-block">Current: {{ $phase->invoice_file }}</span>
                @endif
            @endisset
        </div>
    </div>
</div>
