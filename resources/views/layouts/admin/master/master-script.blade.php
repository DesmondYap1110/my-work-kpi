{{-- Script imports. jQuery must load before the DataTables plugins, and the
     theme libs before sidebar-toggle.js, which drives the hamburger. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="{{ asset('assets/js/datatable/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable/dataTables.responsive.min.js') }}"></script>

<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('js/sidebar-toggle.js') }}"></script>
<script src="{{ asset('js/loader.js') }}"></script>

<script>window.datatablesEndpoint = @json(route('datatables.listing'));</script>
<script src="{{ asset('js/datatables.js') }}"></script>

@yield('js')
@stack('scripts')
