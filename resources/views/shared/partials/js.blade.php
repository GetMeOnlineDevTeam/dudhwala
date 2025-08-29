<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<!--plugins-->
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/plugins/metismenu/metisMenu.min.js') }}"></script>
<script src="{{ asset('assets/plugins/peity/jquery.peity.min.js') }}"></script>

<script src="{{ asset('assets/plugins/simplebar/js/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

{{-- toaster disappear after 4 sec --}}
@if (Session::has('success'))
<script>
    setTimeout(function () {
        const alert = document.querySelector('.alert');
        if (alert) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 4000); // 4 seconds
</script>
@endif