{{-- Script imports. jQuery must load before the DataTables plugins, and the
     theme libs before sidebar-toggle.js, which drives the hamburger. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="{{ asset('assets/js/datatable/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/dataTables.responsive.min.js') }}"></script>

<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>

{{-- App core must come before any module that registers with it. --}}
<script src="{{ \App\Support\Asset::url('js/core.js') }}"></script>

{{-- Template modules - reusable in any project built from this template. --}}
<script src="{{ \App\Support\Asset::url('js/modules/layout.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/loader.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/image-preview.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/unique-check.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/inline-edit.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/mark-list.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/select-or-new.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/inline-form.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/collapse.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/confirm.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/quick-create.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/status-select.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/modal-errors.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/text-peek.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/table-search.js') }}"></script>
<script src="{{ \App\Support\Asset::url('js/modules/kpi-split.js') }}"></script>
<script>window.datatablesEndpoint = @json(route('datatables.listing'));</script>
<script src="{{ \App\Support\Asset::url('js/modules/datatables.js') }}"></script>

{{-- Project modules - replace these when starting a new project. --}}
<script src="{{ \App\Support\Asset::url('js/project/modals.js') }}"></script>

@yield('js')
@stack('scripts')
