<style type="text/css">
	/*GENERAL SECTION*/
	::selection {
		color: #fff;
	    background: #1896bd;
	}
	::-webkit-scrollbar-track {
	    background: rgb(0 0 0 / 30%);
	}
	::-webkit-scrollbar-thumb {
	    background: #1896bd;
	}
	.body-d {
		background: #eaedf7;
	}
	[data-layout=vertical][data-sidebar-size=sm] .navbar-menu .navbar-nav .nav-item:hover>a.sb-menu-d, [data-layout=vertical][data-sidebar=dark][data-sidebar-size=sm] .navbar-menu .navbar-nav .nav-item:hover>.sb-menu-d {
		background-color: #000f2d !important;
	}
	.general-box {
        background: #fff;
        box-shadow: 0 10px 30px 0 rgb(24 28 33 / 5%);
    }
    .btn1 {
    	color: #fff;
		background: #1896bd;
    }
    .btn1:hover {
    	color: #fff;
    	background: #1896bd;
    }
    .btn2 {
    	color: #111;
		background: #efefef;
    }
    .btn2:hover {
    	color: #111;
		background: #efefef;
    }
    .btn3 {
    	color: #fff;
		background: #06d736;
    }
    .btn3:hover {
    	background: #0eb635;
    }
    .btn4 {
    	color: #fff;
		background: #ff4d4d;
    }
    .btn4:hover {
    	background: #ff1c1c;
    }
    .btn5 {
    	color: #fff;
		background: #012161;
    }
    .btn5:hover {
    	background: #012161;
    }
    .btn6 {
    	color: #fff;
		background: #cecece;
		cursor: not-allowed;
    }
    .btn6:hover {
    	color: #fff;
		background: #cecece;
    }
    #general-btn:disabled {
    	color: #fff;
		background: #cecece;
		cursor: not-allowed;
    }
    .gnl-color {
    	color: #F19820;
    }
    .gnl-bg-green {
    	color: #fff;
    	background: #06d736;
    }
    .gnl-red {
    	color: #ff4d4d;
    }

	/*SIDEBAR SECTION*/
	#sb-active {
		color: #fff !important;
		background: linear-gradient(58deg, #1896bd 50%, #24EBC5) !important; 
	}
	#sb-sub-active {
		color: #24ecc5 !important;
	}
	#sb-sub-active:before {
		background: #fff !important;
	}
	.sidebar-d {
		box-shadow: unset !important;
		background: #000f2d !important;
		border-right: unset !important;
	}
	.sidebar-logo-d {
		background: #000f2d !important;
		box-shadow: unset;
	}
	.dropdown-menu {
		background: #fff;
		box-shadow: 0 10px 30px 0 rgb(24 28 33 / 5%);
	}
	.dropdown-item, #stuff-title {
		color: #282828;
	}
	.dropdown-item:hover {
	    color: #fff;
	    background-color: #1896bd;
	    transition: all 0.3s linear;
	}
	.navbar-menu .navbar-nav .menu-link, .navbar-menu .navbar-nav .nav-sm .nav-link {
		color: #d3d3d3 !important;
	}
	.navbar-menu .navbar-nav .menu-link:hover, .navbar-menu .navbar-nav .nav-sm .nav-link:hover {
		color: #fff !important;
	}
	@media (max-width: 991px){
		.sidebar-d {
			backdrop-filter: blur(20px);
		}
	}

	/*BREADCRUMB SECTION*/
	#bc-div {
		color: #282828;
		background: #ffffff26;
    	box-shadow: 0 10px 30px 0 rgb(24 28 33 / 5%);
	}
	#bc-div a {
		color: #282828;
		transition: all 0.3s linear;
	}
	#bc-div a:hover {
		color: #1896bd;
		transform: translateY(-5px);
	}
	#bc-active {
		color: #1896bd;
	}

	/*FORM SECTION*/
	#form-sub-title {
        color: #1896bd;
    }
    label {
    	color: #737c91;
    }
    .form-control, .select2-container--default .select2-selection--single, .select2-container--default .select2-search--dropdown .select2-search__field, .multiselect, .choices__inner, .choices__input, .dataTables_filter input {
    	color: #282828 !important;
	    background: transparent !important;
		border: 1px solid #e9e9e9 !important;
    }
    .form-control:focus, .select2-container--default .select2-selection--single:focus, .select2-container--default .select2-search--dropdown .select2-search__field:focus, .multiselect:focus, .dataTables_filter input:focus {
		border: 1px solid #1896bd !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
		color: #282828 !important;
	}
	.form-control:disabled, .form-control[readonly], .select2-container--default.select2-container--disabled .select2-selection--single {
		background: #01216140 !important;
    	color: #000 !important;
	}
	.select2-container--disabled .select2-selection--single .select2-selection__rendered {
		color: #fff !important;
	}
	.select2-container--default .select2-search--dropdown {
		background: #fff !important;
	}
	.select2-dropdown, .choices__list--dropdown {
		border: unset !important;
    	background: #fff !important;
    }
    .select2-results__option, .choices__list--dropdown .choices__item {
    	color: #282828;
    }
    .select2-container--default .select2-results__option--selected, .select2-container--default .select2-results__option[aria-selected=true]:hover, .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable, .choices__list--dropdown .choices__item--selectable.is-highlighted {
    	color: #fff !important;
	    background-color: #1896bd !important;
	}
	.select2-container--open .select2-selection--single .select2-selection__arrow b {
		border-color: transparent transparent #ffffff transparent!important;
	}
	.form-control::-webkit-file-upload-button, .form-control:hover:not(:disabled):not([readonly])::-webkit-file-upload-button {
        background-color: #1abc9c;
    }
	label span {
        color: #ff4d4d;
    }
    #pw-ct-title {
		color: #f9f9f9;
	}
	#otp-btn {
	    color: #fff;
	    background: #f93ffa;
	}
	#otp-btn:disabled {
	    color: #fff;
	    background: #7f7f7f;
	    cursor: not-allowed;
	}
	#password_contain {
		background: #00415b;
	}
	.valid {
        color: #20b843;
    }
    .invalid {
        color: #ff4444;
    }
    #otp-btn {
        color: #fff;
        background: #f93ffa;
    }
    #otp-btn:disabled {
        color: #fff;
        background: #7f7f7f;
        cursor: not-allowed;
    }
    #link-p,#link-p1 {
        color: #f9f9f9;
    }
    #note-special {
		color: #f9f9f9;
	}
    .note-b {
		color: #b44c36;
		background: #fbd1c8;
	}
	#input-copy {
	    color: #fff;
	    background: #000f2d;
	}
	.toastify {
		background: #00b7ff !important;
    	box-shadow: 0 3px 9px 0 rgb(28 28 51 / 15%) !important;
	}
	.form-switch-success .form-check-input:checked {
	    background-color: #1896bd;
	    border-color: #00d230;
	}
	.avatar-title {
		background: #1896bd;
	}
	.form-check-input {
		background-color: #fff;
    	border: 1px solid #ced4da;
	}
	.form-check-input:checked {
    	border: 1px solid #1896BD;
    	background-color: #1896BD;
	}
	#add-box {
        border: 1px solid #00000026;
    }
    #add-title, #add-p, #add-contact, #sp-p {
        color: #282828;
    }
    #form-address-div .active, #form-address-div .active2 {
    	background: #ff920014 !important;
        border: 1px solid #F19820 !important;
    }
    #form-address-div .active:after, #form-address-div .active2:after {
        color: #fff;
        background: #F19820;
    }
    #add-default {
    	color: #fff;
    	background: #06d736;
    }

    /*TABLE SECTION*/
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: #fff !important;
    }
    .dt-buttons a.dt-button, .dt-buttons button.dt-button, .dt-buttons div.dt-button, .dt-buttons input.dt-button {
	   	color: #fff;
	    background: #00b7ff;
	    border-color: #00b7ff;
	    transition: all 0.3s;
	}
	.dt-buttons a.dt-button:hover, .dt-buttons button.dt-button:hover, .dt-buttons div.dt-button:hover, .dt-buttons input.dt-button:hover {
	    border-color: #001c53 !important;
	    background: #001c53 !important;
	}
	tr th {
		border-color: #ffffff14;
	}
	.table-bordered>:not(caption)>* {
	    border-color: #0000000d;
	}
	.page-item.disabled .page-link, .page-link,.dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
	    color: #282828;
	    background-color: #fff !important;
	    border-color: #0000000d !important;
	}
	.page-item.active .page-link, .page-link:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
		color: #fff !important;
	    background-color: #012161 !important;
    	border-color: #012161 !important;
	}
	#table-div th {
		color: #fff;
		background: #012161;
	}
	#table-div td {
		color: #282828;
		background: #fff;
	}
	#tb-link {
		color: #00b7ff;
		transition: all 0.3s;
	}
	#tb-link:hover {
		text-decoration: underline !important;
	}
	.note-color-1 {
		color: #ff4d4d;
	}
	.note-color-2 {
		color: #222;
	}
	#modal-p b {
		color: #000;
		font-weight: 900;
	}
	#tb-title, #dpc-title {
		color: #1896bd;
	}
	.tb-status {
		color: #fff;
	}
	#tb-status-1 {
		background: #00d230;
	}
	#tb-status-2 {
		background: #ff4d4d;
	}
	#tb-status-3 {
		background: #e18d00;
	}
	#tb-status-4 {
		background: #00b7ff;
	}
	#tb-status-5 {
		background: #e54b90;
	}
	#tb-time-p {
		color: #6e6e6e;
	}
	#rs-icon {
	    color: #fff;
	    background: #55719b;
	}
	#dpc-icon {
		color: #fff;
	}
	#dpc-main {
		color: #fff;
		background: #ff4d4d;
	}
	#dpc-profile {
		color: #00b7ff;
	}
	.tb-ac-btn:hover {
    	color: #fff;
    }
    

    /*Button with Text*/
    
    #tb-ac-btn-1 {
    	background: #00b7ff;
    }
    #tb-ac-btn-2 {
    	background: #ff4d4d;
    }
    #tb-ac-btn-3 {
    	background: #e54b90;
    }
    #tb-ac-btn-4 {
    	background: #2267d1;
    }
    #tb-ac-btn-5 {
    	cursor: not-allowed;
	    background: #cdcdcd;
	}
	#tb-ac-btn-6 {
	    background: #22d65d;
	}
	#tb-ac-btn-7 {
		background: #e4d900;
	}
    .dataTables_length .form-select {
    	color: #282828;
    	background-color: #fff;
    	border-color: #0000000d;
    }
    .btn-soft-secondary:active, .btn-soft-secondary:focus, .btn-soft-secondary:hover {
	    background-color: #1896bd;
	}
	.tb-label-red {
	    color: #fff;
		background: #ff4d4d;
	}
	.tb-label-green {
	    color: #fff;
		background: #00d230;
	}
	.tb-label-grey {
	    color: #fff;
		background: #787878;
	}
	.tb-label-yellow {
		color: #fff;
		background: #e18d00;
	}
	.tb-label-red {
		color: #fff;
		background: #ff4d4d;
	}
	.tb-label-purple, #dpc-selected {
		color: #fff;
		background: #6259ca;
	}
	#tb-link {
		color: #00b7ff;
		transition: all 0.3s;
	}
	#tb-link:hover {
		text-decoration: underline;
	}
	#tb-red-p, .tb-red-p {
		color: #ff4d4d;
	}
	#tb-green-p, .tb-green-p  {
		color: #00d230;
	}
	#tb-total {
		color: #fff;
		background: #55719b;
	}
	#tb-contributor-box {
		border-bottom: 1px dashed #ffffff26;
	}
	#tb-contributor-box:last-child {
		border-bottom: unset;
	}
	#tb-contributor-title {
		color: #fff;
	}
	#tb-contributor-p {
		color: #f9f9f9;
	}
	#tb-input-div input {
	    background: #ffffff33 !important;
	}
	.tb-title-td {
		background: #e7e7e7 !important;
	}
	div.dataTables_wrapper div.dataTables_info {
		color: #282828;
	}
	.green {
		color: #06d736;
	}
	#st-rm {
		color: #9c9c9c;
	}
	#tk-link {
		color: #00b7ff;
		text-decoration: underline !important;
	}

	/*FILTER SECTION*/
	#tb-border-line {
		border-bottom: 1px dashed #00000026;
	}
	.flatpickr-calendar {
	    background: #fff;
    	box-shadow: 0 10px 30px 0 rgb(24 28 33 / 5%);
	}
	.flatpickr-day.endRange.startRange+.endRange:not(:nth-child(7n+1)), .flatpickr-day.selected.startRange+.endRange:not(:nth-child(7n+1)), .flatpickr-day.startRange.startRange+.endRange:not(:nth-child(7n+1)), .flatpickr-day.inRange {
	    box-shadow: -10px 0 0 #012161;
	}
	.flatpickr-months, .flatpickr-weekdays {
		background-color: #012161;
	}
	.flatpickr-day {
		color: #282828;
	}
	.flatpickr-day.inRange, .flatpickr-day.nextMonthDay.inRange, .flatpickr-day.nextMonthDay.today.inRange, .flatpickr-day.nextMonthDay:focus, .flatpickr-day.nextMonthDay:hover, .flatpickr-day.prevMonthDay.inRange, .flatpickr-day.prevMonthDay.today.inRange, .flatpickr-day.prevMonthDay:focus, .flatpickr-day.prevMonthDay:hover, .flatpickr-day.today.inRange, .flatpickr-day:focus, .flatpickr-day:hover {
		color: #fff;
		background: #012161;
		border-color: #012161;
	}
	.flatpickr-day.endRange, .flatpickr-day.endRange.inRange, .flatpickr-day.endRange.nextMonthDay, .flatpickr-day.endRange.prevMonthDay, .flatpickr-day.endRange:focus, .flatpickr-day.endRange:hover, .flatpickr-day.selected, .flatpickr-day.selected.inRange, .flatpickr-day.selected.nextMonthDay, .flatpickr-day.selected.prevMonthDay, .flatpickr-day.selected:focus, .flatpickr-day.selected:hover, .flatpickr-day.startRange, .flatpickr-day.startRange.inRange, .flatpickr-day.startRange.nextMonthDay, .flatpickr-day.startRange.prevMonthDay, .flatpickr-day.startRange:focus, .flatpickr-day.startRange:hover {
		color: #fff;
		background: #012161;
		border-color: #012161;
	}
	#filter-btn-div a, #filter-btn-div button {
		border: unset;
	}

	/*MODAL SECTION*/
	.modal-content {
		border: unset;
	}
	#modal-title {
		color: #282828;
	}
	#modal-p {
		color: #383838;
	}
	#md-border-line {
		border-top: 1px dashed #ffffff26;
	}
	#md-qrcode-title {
		color: #fff;
	}
	.modal-dialog:not(.modal-dialog-scrollable) .modal-header {
	    border-bottom: 1px solid #0000000d;
	}
	#modal-btn-div {
		border-top: 1px solid #0000000d;
	}
	.input-group-border-top {
	    border-top: 1px solid #0000000d;
	}
	.input-group-border-bottom {
	    border-bottom: 1px solid #0000000d;
	}
	#md-symbol-div {
		color: #f9f9f9;
	}
	#md-symbol-div span {
		color: #00b7ff;
	}
	[data-layout-mode=dark] .btn-close {
		filter: unset;
	}

	/*HEADER SECTION*/
	.header-d {
		background: #fff !important;
	    box-shadow: 0px 10px 30px rgb(0 0 0 / 5%);
	}
	#page-topbar.topbar-shadow {
		box-shadow: unset;
	}
	.btn-ghost-secondary {
		color: #282828;
	}
	.btn-ghost-secondary:active, .btn-ghost-secondary:focus, .btn-ghost-secondary:hover {
	    color: #1896bd;
	    background-color: rgb(255 255 255 / 5%);
	}
	.bg-pattern {
		background: url(../assets/images/modal-bg.png);
		background-color: #012161 !important;
	}
	.text-secondary {
	    color: #4be8d4 !important;
	}
	.nav-tabs-custom .nav-item .nav-link.active {
	    color: #fff;
	}
	.dropdown-head .nav-tabs-custom .nav-link.active {
	    background-color: #ffffff26;
	}
	#notification-title {
		color: #282828;
	}
	#notification-p {
		color: #6e6e6e;
	}
	#notification-time {
		color: #012161;
	}
	.topbar-user {
	    background-color: unset;
	}
	#hd-username {
		color: #282828;
	}
	#hd-position {
		color: #1896bd;
	}
	.hd-wallet-active {
    	color: #fff;
		background: rgb(255 255 255 / 15%) !important;
	}
	.hd-wallet-active i {
    	color: #fff !important;
	}
	.topbar-badge {
		background-color: #e60000 !important;
	}

	/*FOOTER SECTION*/
	#footer-p {
		color: #6e6e6e;
	}

	/*MOBILE MENU SECTION*/
    @media (max-width: 991px){
    	#mm-section {
			background: rgb(15 19 63 / 70%);
			border-image: linear-gradient(to right, #be0000, #F19820);
		}
		#mm-section ul li a #mm-icon {
			color: #fff;
		}
		#mm-section ul li a #mm-text {
			color: #f9f9f9;
		}
		#mm-indicator {
			background: linear-gradient(58deg, #1896BD 50%, #24EBC5) !important;
		}
    }

/***** HOMEPAGE *****/

    /*FEATURES SECTION*/
    #ft-title {
        color: #f9f9f9;
    }
    #ft-box {
    	background: #012161;
    	box-shadow: 0 10px 15px -2px rgb(82 0 57 / 8%);
    }
    #ft-box:after {
        background: linear-gradient(58deg, #1896bd 50%, #24EBC5) !important;
    }
    #ft-p {
        color: #fff;
        text-shadow: 2px 2px 6px #ffffff33;
    }
    #ft-icon {
    	color: #fff;
    	background: #ffffff26;
    	box-shadow: 0 10px 15px -2px rgb(82 0 57 / 8%);
    }

    /*PIE CHART SECTION*/
    #pc-box-div span {
        color: #282828;
    }
    .pc-color-1 {
    	background: #bc20f1;
    }
    .pc-color-2 {
    	background: #00b7ff;
    }
    .pc-color-3 {
    	background: #e54b90;
    }
    .pc-color-4 {
    	background: #205ef1;
    }
    .pc-color-5 {
    	background: #e18d00;
    }
    .pc-color-6 {
    	background: #ff6868;
    }
    .pc-color-7 {
    	background: #1cdbe8;
    }
    .pc-color-8 {
    	background: #00d230;
    }

/***** WAREHOUSE ADD *****/
	#rate-div {
        color: #282828;
    }

/***** INVOICE *****/
	#iv-logo-name, #iv-info-p, #iv-note-p, #iv-title, #iv-add-p, #iv-add-title {
        color: #282828;
    }
    #iv-border-line {
        border-top: 1px solid #00000014;
    }
    @media print {
        #table-div th {
            background: #000 !important;
        }
    }

/***** CLIENT EDIT *****/
	#point-box {
	    background: #012161;
	    box-shadow: 0 10px 15px -2px rgb(82 0 57 / 8%);
	}
	#point-p {
	    color: #fff;
	    background: -webkit-linear-gradient(#fff, #BDBEC0);
	    -webkit-background-clip: text;
	    -webkit-text-fill-color: transparent;
	}
	#point-title {
	    color: #F19820;
	}

/***** STUFFING *****/
	#stff-info-p {
        color: #282828;
    }
    #stff-info-p b {
        color: #000;
    }
</style>