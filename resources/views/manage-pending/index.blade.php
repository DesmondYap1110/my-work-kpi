@extends('layouts.app')

@section('title', 'Manage Pending')

@section('content')
    {!! show_datatables('ManagePendingList') !!}

    <div class="modal fade" id="rejectPendingModal" tabindex="-1" data-url-template="{{ route('manage-pending.reject', '__id__') }}">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form method="POST">
                    @csrf
                    <div class="modal-header">
                        <p id="modal-title">Reject KPI</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <p id="modal-p" style="margin-bottom: 5px;">Are you sure you want to reject this entry?</p>
                        <div class="input-group mb-0">
                            <label>Mark<span>*</span></label>
                            <select class="form-control" name="mark" required></select>
                        </div>
                    </div>
                    <div id="modal-btn-div">
                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal"><i class="ri-close-fill"></i>Cancel</a>
                        <button type="submit" id="general-btn" class="btn4"><i class="ri-close-circle-line"></i>Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
