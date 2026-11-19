<!-- jQuery (necesario para DataTables y SweetAlert helpers) -->
<script src="{{ asset('AdminLTE/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap bundle (para modales/dropdowns AdminLTE si se mezclan) -->
<script src="{{ asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Toastr -->
<script src="{{ asset('AdminLTE/plugins/toastr/toastr.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('AdminLTE/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
$(function () {
    // Configuración Toastr
    toastr.options = {
        closeButton:   true,
        progressBar:   true,
        positionClass: 'toast-top-right',
        timeOut:       4000,
    };

    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif

    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif
});
</script>
