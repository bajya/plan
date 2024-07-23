<script src="{{ asset('frontend/js/core/popper.min.js') }}"></script>
<script src="{{ asset('frontend/js/core/bootstrap.min.js') }}"></script>
<script src="{{ asset('frontend/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('frontend/js/plugins/smooth-scrollbar.min.js') }}"></script>
<script src="{{ asset('frontend/js/plugins/dragula/dragula.min.js') }}"></script>
<script src="{{ asset('frontend/js/plugins/jkanban/jkanban.js') }}"></script>
<script>
	var win = navigator.platform.indexOf('Win') > -1;
	if (win && document.querySelector('#sidenav-scrollbar')) {
		var options = {
		    damping: '0.5'
		}
		Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
	}
</script>
<script async defer src="https://buttons.github.io/buttons.js"></script>
<script src="{{ asset('frontend/js/soft-ui-dashboard.min.js?v=1.0.5') }}"></script>