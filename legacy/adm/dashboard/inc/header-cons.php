<link href="<?php echo $base_url; ?>/assets/libs/jsvectormap/css/jsvectormap.min.css" rel="stylesheet" type="text/css">
<link href="<?php echo $base_url; ?>/assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css">
<script src="<?php echo $base_url; ?>/assets/js/layout.js"></script>
<link href="<?php echo $base_url; ?>/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="<?php echo $base_url; ?>/assets/css/icons.min.css" rel="stylesheet" type="text/css">
<link href="<?php echo $base_url; ?>/assets/css/app.min.css" rel="stylesheet" type="text/css">
<link href="<?php echo $base_url; ?>/assets/css/custom.min.css" rel="stylesheet" type="text/css">

<link rel="apple-touch-icon" sizes="144x144" href="<?php echo $base_url; ?>/img/apple-touch-icon-144x144.png">
<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $base_url; ?>/img/apple-touch-icon-114x114.png">
<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $base_url; ?>/img/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="57x57" href="<?php echo $base_url; ?>/img/apple-touch-icon-57x57.png">

<link rel="icon" sizes="32x32" href="<?php echo $base_url; ?>/img/favicon-32x32.png">
<link rel="icon" sizes="192x192" href="<?php echo $base_url; ?>/img/favicon-192x192.png">

<!-- SELECT2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<meta name="theme-color" content="#fff">
<style type="text/css">
	/*GENERAL SECTION*/
	h1, h2, h3, h4, h5, h6, span, button, a, li, p {
	    font-family: 'Roboto', sans-serif !important;
	}
	button {
		border: unset;
	}
    #general-section {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
	::-webkit-scrollbar {
	    width: 3px;
	}
	::-webkit-scrollbar-track, ::-webkit-scrollbar-thumb {
	    border-radius: 10px;
	}
	.page-content {
	    padding: calc(0px + 0rem) calc(1.5rem / 2) 4.5rem calc(1.5rem / 2);
	}
	#general-btn {
		font-size: 12px;
		font-weight: 500;
		line-height: 1.4;
		padding: 11px 20px;
		border-radius: 3px;
		text-align: center;
		position: relative;
		letter-spacing: 1px;
		align-items: center;
		display: inline-flex;
		justify-content: center;
		transition: all 0.3s linear;
	}
	#general-btn:hover {
		transform: translateY(-5px);
	}
	#general-btn i {
		margin-right: 8px;
	}
	#general-note {
		display: block;
		font-size: 12px;
		line-height: 1.4;
		margin-bottom: 0;
	}
	@media (max-width: 991px){
		.page-content {
		    padding: calc(0px + 0rem) calc(0rem / 2) 9rem calc(0rem / 2);
		}
		#general-btn {
			font-size: 11px;
		}
		#general-note {
		    font-size: 9px;
		}
	}

	/*SIDEBAR SECTION*/
	#sb-active {
		margin-right: 10px;
		border-radius: 0 60px 60px 0;
	}
	.sb-bg-logo
    {
		width: 150px;
    	height: auto;
	}
    .sb-sm-logo
    {
        width: 60px;
    	height: auto;
    }
     
    
	[data-layout=vertical][data-sidebar=dark][data-sidebar-size=sm] .navbar-brand-box {
		background: unset;
	}
	.navbar-nav {
		padding-top: 30px;
	}
	.navbar-menu .navbar-nav .menu-link {
		padding: 10px;
		font-size: 13px;
	    font-weight: 600;
	    line-height: 1.4;
	}
	.navbar-menu .navbar-nav .menu-link i {
		font-size: 15px;
		min-width: unset;
		margin-right: 8px;
	}
	.navbar-menu .navbar-nav .nav-sm .nav-link {
		font-size: 12px;
	    font-weight: 500;
	    padding: 7px 5px 7px 15px !important;
	}
	.navbar-menu .navbar-nav .nav-sm .nav-link:before {
		left: 0;
		top: 15px;
	}
	#sb-mb-logo-div {
		display: none;
	}
	.navbar-menu .navbar-nav .nav-sm {
	    padding-left: 15px;
	}
	@media (max-width: 991px){
		#sb-mb-logo-div {
			display: block;
			text-align: center;
			padding-top: 15px;
		}
		#sb-mb-logo-div img {
			width: 55px;
			height: auto;
		}
	}

	/*BREADCRUMB SECTION*/
	#bc-section {
		padding: 70px 0 0.5rem;
		position: relative;
	}
	#bc-div {
		padding: 15px 25px;
		font-size: 12px;
		line-height: 1.4;
	}
	#bc-div i {
		top: 2px;
		position: relative;
	}
	#bc-arrow {
		margin: 0 5px;
	}
	@media (max-width: 991px){
		#bc-div {
			font-size: 9px;
		    padding: 10px 20px;
		}
		#bc-arrow {
			margin: 0 2px;
		}
	}

	/*FORM SECTION*/
	#form-box {
        padding: 25px 15px 25px;
        border-radius: 5px;
    }
    #form-div {
        margin-bottom: 30px;
    }
    #form-sub-title {
        font-size: 18px;
        font-weight: 600;
        line-height: 1.4;
        margin-bottom: 15px;
    }
	.input-group {
        display: block;
        flex-wrap: unset;
        margin-bottom: 15px;
        -webkit-box-align: unset;
    }
    .form-control, .select2-container--default .select2-selection--single, .multiselect, .choices__inner, .dataTables_filter input {
        width: 100% !important;
        height: 40px !important;
        border: unset !important;
        font-size: 12px;
        padding: 0 10px;
        line-height: 1.4;
        transition: all 0.3s;
        border-radius: 0.25rem !important;
    }
    .choices[data-type*=select-one] .choices__inner {
    	padding-bottom: 0;
    }
    .choices__list--single {
    	padding: 0;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered, .choices__list--single {
        padding: 0 !important;
        line-height: 40px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 0 !important;
        height: 40px !important;
    }
    .form-control:disabled, .select2-container--default.select2-container--disabled .select2-selection--single {
		cursor: not-allowed !important;
	}
    .input-group .file-input {
        line-height: 40px;
    }
	.select2-dropdown {
		z-index: 999999 !important;
	}
	.choices__list--multiple .choices__item {
		margin-top: 10px;
	}
	label span {
		margin-left: 3px;
	}
	#note-p {
		font-size: 11px;
		line-height: 1.4;
		margin-bottom: 0;
	}
	#form-btn-div {
		margin-top: 15px;
	}
	#form-btn-div a, #form-btn-div button {
		border: unset;
		margin-right: 5px;
	}
	#form-btn-div a:last-child, #form-btn-div button:last-child {
		margin-right: 0;
	}
	#pw-ct-title {
		font-size: 13px;
	    line-height: 1.4;
	    margin-bottom: 0.5rem;
	    padding-left: 0 !important;
	}
	#password_contain {
        display:none;
        padding: 15px;
        border-radius: 5px;
        position: relative;
        margin-bottom: 0.5rem;
    }
    #pw-ct-title {
        font-size: 13px;
        font-weight: 500;
        line-height: 1.4;
        margin-bottom: 0.5rem;
        padding-left: 0 !important;
    }
    #password_contain .invalid, #password_contain .valid {
        font-size: 12px;
        line-height: 1.4;
        margin-left: 5px;
        margin-bottom: 5px;
    }
    #password_contain p:last-child {
        margin-bottom: 0;
    }
    .valid:before {
        position: relative;
        left: -5px;
        content: "✔";
    }
    .invalid:before {
        position: relative;
        left: -5px;
        content: "✖";
    }
	.pw-ct-p {
		font-size: 11px;
		line-height: 1.4;
		margin-bottom: 5px;
	}
	.pw-ct-p:last-child {
		margin-bottom: 0;
	}
	#psw-div {
		position: relative;
	}
	#psw-visable-icon {
	    top: 0;
	    right: 10px;
	    height: 100%;
	    display: flex;
	    align-items: center;
	    cursor: pointer;
	    position: absolute;
	}
	#note-special {
		font-size: 12px;
	    line-height: 1.4;
	    margin-bottom: 0;
	    display: block;
	}
	.note-b {
		font-size: 10px;
		font-weight: 600;
		padding: 2px 4px;
		border-radius: 2px;
		display: inline-block;
	}
	#form-copy-div {
		align-items: center;
		display: flex;
	}
	#form-copy-div .form-control {
		border-radius: 0.25rem 0 0 0.25rem !important;
	}
	#input-copy {
		cursor: pointer;
	    height: 40px;
	    display: flex;
	    font-size: 12px;
	    padding: 0 10px;
	    line-height: 1.4;
	    align-items: center;
		border-radius: 0 0.25rem 0.25rem 0 !important;
	}
	.btn-group {
		display: block;
	}
	.multiselect {
		text-align: left;
	}
	.dropdown-toggle::after {
		right: 15px;
		margin-left: unset;
	    line-height: unset;
		position: absolute;
	}
	.multiple-group .dropdown-menu {
        width: 100%;
        padding: 15px 15px 5px;
    }
    .multiselect-container>li>a>label {
        width: 100%;
        padding: 0 !important;
        margin-bottom: 10px !important;
    }
    .open > .dropdown-menu {
        display: block;
    }
    .open > a {
        outline: 0;
    }
    .multiple-group .multiselect-container>li>a>label>input[type=checkbox] {
	    margin-bottom: 0;
	    position: relative;
	    top: 2px;
	    margin-right: 3px;
	}
	#form-checkbox-div {
		margin-bottom: 25px;
	}
	#form-checkbox-div input {
		width: 3.5em;
    	height: 1.5em;
	}
	#form-checkbox-div label {
		margin-left: 5px;
    	margin-top: 2px;
	}
	#profile-img-div {
		position: relative;
		display: inline-block;
		margin-bottom: 15px;
	}
	#profile-img-div img {
		width: 120px;
	    height: 120px;
	    border-radius: 50%;
	}
	#rate-div {
        font-size: 13px;
        line-height: 3;
        margin-bottom: 10px;
    }
    #rate-div:last-child {
        margin-bottom: 0;
    }
    #rate-div .form-control {
        margin: 0 5px;
        text-align: center;
        width: 15% !important;
        display: inline-block;
        height: 30px !important;
    }
    #form-address-div {
    	overflow: auto;
    	max-height: 380px;
    }
    #add-box {
        padding: 15px;
        cursor: pointer;
        position: relative;
        transition: all 0.5s;
        margin-bottom: 15px;
        border-radius: 0.25rem;
    }
    #add-title {
        font-size: 15px;
        font-weight: 700;
        line-height: 1.4;
        margin-bottom: 10px;
    }
    #add-p {
        font-size: 12px;
        line-height: 1.4;
        margin-bottom: 0;
    }
    #add-contact {
        font-size: 13px;
        line-height: 1.4;
        margin-top: 10px;
        margin-bottom: 0;
    }
    #add-title b, #add-contact b {
        margin-right: 5px;
    }
    #form-address-div .active:after, #form-address-div .active2:after {
        right: -1px;
        bottom: -1px;
        width: 20px;
        height: 20px;
        font-size: 13px;
        content: "\eb7b";
        font-weight: 600;
        text-align: center;
        position: absolute;
        transition: all 0.5s;
        border-radius: 0 0 0.25rem 0;
        font-family: remixicon !important;
    }
    #add-default {
    	top: 15px;
    	right: 15px;
    	font-size: 9px;
	    font-weight: 500;
	    line-height: 1.4;
	    padding: 2px 5px;
	    border-radius: 2px;
	    position: relative;
	    position: absolute;
	    display: inline-block;
    }
	@media (max-width: 991px){
		#form-box {
            margin-bottom: 15px;
        }
        #form-div {
            margin-bottom: 20px;
        }
        #form-sub-title {
            font-size: 15px;
        }
        label {
            font-size: 10px;
        }
        .form-control, .select2-container--default .select2-selection--single, .multiselect, .choices__inner, .select2-results__option, .choices__list--dropdown .choices__item, .dataTables_filter input {
            font-size: 11px;
        }
        .choices[data-type*=select-one] .choices__input {
        	font-size: 11px;
        	padding: 5px;
        }
        #note-special {
		    font-size: 8px;
		}
		.note-b {
			font-size: 7px;
		}
		.multiple-group .multiselect-container>li>a>label>input[type=checkbox] {
		    margin-bottom: 0;
		    position: relative;
		    top: 2px;
		    margin-right: 3px;
		    width: 10px;
		    height: 10px;
		}
		#profile-img-div img {
		    width: 100px;
		    height: 100px;
		}
		#rate-div {
            font-size: 10px;
            margin-bottom: 0;
        }
        #rate-div .form-control {
            font-size: 10px;
            width: 20% !important;
            height: 20px !important;
        }
        #form-address-div {
		    max-height: 335px;
		}
        #add-title {
    		font-size: 13px;
    	}
    	#add-default {
		    font-size: 7px;
		}
		#add-p {
    		font-size: 10px;
    	}
    	#add-contact {
    		font-size: 11px;
    	}
    }

	/*TABLE SECTION*/
	#table-div table {
		width: 100% !important;
		margin-bottom: 0;
	}
	.dataTables_empty {
        display: none !important;
    }
    div.dataTables_wrapper div.dataTables_filter input {
        width: 200px !important;
        height: 30px !important;
    }
    #table-div th, #table-div td {    
        padding: 8px;
        font-size: 12px;
        vertical-align: middle;
    }
    #table-div th {    
        font-weight: 600;
    }
    #tb-act-btn-div button {
    	border: unset;
    }
    .tb-ac-btn {
	    color: #fff;
	    width: 35px;
	    height: 35px;
	    font-size: 13px;
	    line-height: 1.4;
	    margin-right: 1px;
	    border-radius: 2px;
	    align-items: center;
	    display: inline-flex;
	    transition: all 0.3s;
	    justify-content: center;
    }
    .tb-ac-btn:last-child {
    	margin-right: 0;
    }
    .tb-ac-btn:hover {
    	transform: translateY(-5px);
    }

    /*Desmond Edit*/
    .tb-btn-rel 
    {
        position: relative;
    }
    .tb-in-ac-btn{
        color: #1896BD;
        position: absolute;
        right: 8px;
        font-size: 20px;
        top: 9px;
    }
    @media (max-width: 991px){
        .tb-in-ac-btn {
            font-size: 13px;
            position: relative;
            right: 0;
            top: 2px;
            padding-left: 10px;
        }
    }
    /*Desmond Edit*/

    #tb-add-btn-div {
    	text-align: right;
    	margin-bottom: 15px;
    }
    #tb-add-btn-div a {
	    font-size: 10px !important;
	    padding: 10px 15px !important;
	    margin-right: 8px;
	}
	#tb-add-btn-div a:last-child {
	    margin-right: 0;
	}
	#tb-add-btn-div a i {
		font-weight: 600;
	    margin-right: 5px !important;
	}
	#tb-title {
		font-size: 15px;
		font-weight: 600;
		line-height: 1.4;
		margin-bottom: 15px;
	}
	.tb-status {
	    font-size: 10px;
	    font-weight: 500;
	    line-height: 1.4;
	    padding: 3px 5px;
	    border-radius: 2px;
	    display: inline-block;
	}
	#table-padding {
		padding: 0 15px;
	}
	table.dataTable>thead .sorting:before, table.dataTable>thead .sorting_asc:before, table.dataTable>thead .sorting_asc_disabled:before, table.dataTable>thead .sorting_desc:before, table.dataTable>thead .sorting_desc_disabled:before {
		top: 3px;
	}
	table.dataTable>thead .sorting:after, table.dataTable>thead .sorting_asc:after, table.dataTable>thead .sorting_asc_disabled:after, table.dataTable>thead .sorting_desc:after, table.dataTable>thead .sorting_desc_disabled:after {
	    top: 7px;
	}
	#tb-time-p {
		display: block;
		font-size: 10px;
		line-height: 1.4;
		margin-bottom: 0;
	}
	#tb-box {
        padding: 25px 0;
        border-radius: 5px;
    }
    #tb-add-btn-div {
    	text-align: right;
    }
    .alert p:last-child {
    	margin-bottom: 0;
    }
    #rs-icon {
	    width: 20px;
	    height: 20px;
	    font-size: 9px;
	    cursor: pointer;
	    margin-left: 5px;
	    line-height: 20px;
	    border-radius: 50px;
	    align-items: center;
	    display: inline-flex;
	    justify-content: center;
	}
	#dpc-icon {
		font-size: 20px;
		cursor: pointer;
		transition: all 0.3s;
	}
	#sub-table {
		display: none;
	}
	#dpc-main, #dpc-selected {
	    bottom: 2px;
	    margin-left: 7px;
	    font-size: 9px;
	    font-weight: 500;
	    line-height: 1.4;
	    padding: 2px 5px;
	    border-radius: 2px;
	    position: relative;
	    display: inline-block;
	}
	#dpc-title {
		font-size: 15px;
		font-weight: 600;
		line-height: 1.4;
		margin-top: 15px;
		margin-bottom: 5px;	
	}
	#dpc-profile {
		margin-left: 5px;
		transition: all 0.3s;
	}
	.dropdown-item {
	    font-size: 12px;
	}
	#mt4-acc i {
		top: 2px;
		margin: 0 3px;
		position: relative;
	}
	#tb-total {
		font-size: 13px;
	    font-weight: 700;
	    padding: 7px 12px;
	    border-radius: 3px;
	    display: inline-block;
	}
	#tb-total-td {
		padding: 15px !important;
	}
	#tb-total-btn-div {
		text-align: center;
	}
	#tb-total-btn-div a, #tb-total-btn-div button {
		margin-right: 3px;
		border: unset;
	}
	#tb-total-btn-div a:last-child, #tb-total-btn-div button:last-child {
		margin-right: 0;
	}
	#tb-contributor-box {
		margin-bottom: 10px;
		padding-bottom: 10px;
	}
	#tb-contributor-box:last-child {
		margin-bottom: 0;
		padding-bottom: 0;
	}
	#tb-contributor-title {
		font-size: 13px;
		font-weight: 600;
		line-height: 1.4;
		margin-bottom: 3px;
	}
	.tb-contributor-status {
	    font-size: 10px;
	    font-weight: 500;
	    line-height: 1.4;
	    padding: 1px 5px;
	    border-radius: 2px;
	    display: inline-block;
	}
	#tb-contributor-p {
		font-size: 10px;
	    line-height: 1.4;
	    margin-bottom: 0;
	    display: inline-block;
	}
	#md-symbol-div {
		font-size: 13px;
		line-height: 1.4;
	}
	#md-symbol-btn-div {
		margin-top: 15px;
	}
	#add-btn-div {
        text-align: right;
        margin-bottom: 15px;
    }
    #tb-status-btn-div {
    	display: inline-block;
    }
    #tb-status-btn-div button {
    	font-size: 11px;
	    padding: 9px 15px;
	}
	#tb-status-btn-div button:hover {
	    transform: unset;
	}
	#table-div::-webkit-scrollbar, .dataTables_scrollBody::-webkit-scrollbar {
        height: 8px !important;
    }
    #break-word {
    	word-wrap: break-word;
	    white-space: normal;
	    overflow: hidden;
	    display: -webkit-box;
	    text-overflow: ellipsis;
	    -webkit-box-orient: vertical;
	    -webkit-line-clamp: 2;
    }
    .ssm {
    	font-size: 20px;
    }
    #tb-label-div {
    	display: block;
    }
    #tb-label {
	    font-size: 9px;
	    font-weight: 500;
	    line-height: 1.4;
	    padding: 2px 5px;
	    border-radius: 2px;
	    position: relative;
	    display: inline-block;
	}
	.table .form-check {
		text-align: center;
	}
	#st-rm {
		display: block;
		font-size: 10px;
		margin-top: 5px;
		line-height: 1.4;
		margin-bottom: 0;
	}
    @media (max-width: 991px){
    	#table-div {
			overflow-x: auto;
		}
		#table-div table {
			width: 200% !important;
		}
		#table-div th, #table-div td {
		    font-size: 9px;
		}
		.tb-ac-btn {
		    width: 25px;
		    height: 25px;
		    font-size: 10px;
		}
		#tb-add-btn-div {
		    text-align: left;
		    margin-bottom: 10px;
		}
		#tb-add-btn-div a {
		    font-size: 8px !important;
		    padding: 8px 12px !important;
		}
		button.dt-button {
			margin-bottom: 15px !important;
		}
		div.dt-buttons, div.dataTables_wrapper div.dataTables_filter, div.dataTables_wrapper div.dataTables_info, div.dataTables_wrapper div.dataTables_paginate {
		    text-align: left !important;
		}
		div.dataTables_wrapper div.dataTables_paginate ul.pagination {
		    justify-content: flex-start !important;
		}
		.tb-status {
		    font-size: 8px;
		}
		table.dataTable>thead .sorting:before, table.dataTable>thead .sorting_asc:before, table.dataTable>thead .sorting_asc_disabled:before, table.dataTable>thead .sorting_desc:before, table.dataTable>thead .sorting_desc_disabled:before, table.dataTable>thead .sorting:after, table.dataTable>thead .sorting_asc:after, table.dataTable>thead .sorting_asc_disabled:after, table.dataTable>thead .sorting_desc:after, table.dataTable>thead .sorting_desc_disabled:after {
			font-size: 10px;
		}
		div.dataTables_wrapper div.dataTables_info {
		    padding-top: 5px;
		    padding-bottom: 5px;
		}
		.alert {
		    padding: 8px
		}
		.alert p {
		   	font-size: 11px;
		}
		#rs-icon {
    		width: 16px;
		    height: 16px;
		    font-size: 7px;
		    line-height: 16px;
		}
		#dpc-main, #dpc-selected {
		    font-size: 7px;
		}
		#dpc-title {
			font-size: 13px;
			margin-top: 5px;
			margin-bottom: 5px;
		}
		#mt4-acc {
			margin-left: 20px;
		}
		#tb-title {
	    	font-size: 13px;
	    }
	    #tb-total {
	    	font-size: 11px;
	    	padding: 5px 8px;
	    }
	    #tb-contributor-title {
			font-size: 11px;
		}
		.tb-contributor-status {
		    font-size: 8px;
		}
		#tb-contributor-p {
			font-size: 8px;
		}
		#tb-input-div input {
		    height: 35px !important;
		}
		#md-symbol-div {
    		font-size: 10px;
    	}
    	#add-btn-div {
	        text-align: left;
	    }
	    #tb-status-btn-div button {
		    font-size: 9px;
		    padding: 7px 12px;
		}
		.ssm {
		    font-size: 14px;
		}
		#tb-time-p {
			font-size: 8px;
		}
		#note-p {
    		font-size: 9px;
    	}
    	#st-rm {
		    font-size: 8px;
		}
	}

    /*FILTER SECTION*/
    #tb-border-line {
	    padding: 0 15px 10px;
	    margin-bottom: 25px;
	}
	#filter-btn-div {
		margin-top: 12px;
	}
	#filter-btn-div a, #filter-btn-div button {
		margin-right: 8px;
	}
	#filter-btn-div a:last-child, #filter-btn-div button:last-child {
		margin-right: 0;
	}
	@media (max-width: 991px){
		#tb-border-line {
		    padding: 0 15px 0;
		    margin-bottom: 20px;
		}
		#filter-btn-div {
			display: flex;
		    margin-top: 5px;
		    margin-bottom: 15px;
		}
		#filter-btn-div a, #filter-btn-div button {
		    width: 100%;
		}
	}

    /*MODAL SECTION*/
    #md-dialog {
    	max-width: 500px;
    }
    #md-content {
    	width: 500px;
    }
    #doc-dialog {
    	max-width: 600px;
    }
    #doc-content {
    	width: 600px;
    }
    #dp-dialog {
    	max-width: 800px;
    }
    #dp-content {
    	width: 800px;
    }
    #wd-dialog {
    	max-width: 700px;
    }
    #wd-content {
    	width: 700px;
    }
    #tb-dialog {
    	max-width: 850px;
    }
    #tb-content {
    	width: 850px;
    }
    #fmng-dialog {
    	max-width: 1100px;
    }
    #fmng-content {
    	width: 1100px;
    }
    .wd-overflow {
    	overflow: auto;
    	max-height: 330px;
    }
    .dp-overflow {
    	overflow: auto;
    	max-height: 410px;
    }
    .partial-overflow {
    	overflow: auto;
    	max-height: 390px;
    }
    #modal-div {
	    position: relative;
	    -webkit-box-flex: 1;
	    flex: 1 1 auto;
	    padding: 15px;
	}
	.modal-dialog:not(.modal-dialog-scrollable) .modal-header {
	    padding-bottom: 15px;
	}
	#modal-title {
		font-size: 16px;
		font-weight: 600;
		line-height: 1.4;
		margin-bottom: 0;
	}
	#modal-btn-div {
		padding: 15px;
		display: flex;
		justify-content: space-between;
	}
	#modal-btn-div button {
		border: unset;
	}
	#modal-p {
		font-size: 15px;
		line-height: 1.4;
		margin-bottom: 15px;
	}
	#modal-div p:last-child {
		margin-bottom: 0;
	}
	#md-border-line {
		margin-top: 15px;
		margin-bottom: 15px;
	}
	#md-qrcode-div {
		margin-top: 15px;
		text-align: center;
	}
	#md-qrcode-img-div img {
		width: 150px;
		height: auto;
		margin-bottom: 25px;
	}
	#md-qrcode-title {
		font-size: 13px;
		font-weight: 600;
		line-height: 1.4;
		margin-bottom: 8px;
	}
	.input-group-border-top {
		padding-top: 15px;
	}
	.input-group-border-bottom {
		padding-bottom: 15px;
	}
	@media (max-width: 991px){
		#md-dialog, #doc-dialog, #dp-dialog, #tb-dialog, #fmng-dialog {
	    	max-width: 100%;
	    }
	    #md-content, #doc-content, #dp-content, #tb-content, #fmng-content {
	    	width: 100%;
	    }
		#modal-title {
    		font-size: 14px;
    	}
		.ba-overflow {
		    overflow: auto;
		    max-height: 350px;
		}
		.dp-overflow {
		    overflow: auto;
		    max-height: 350px;
		}
		.wd-overflow {
			overflow: auto;
		    max-height: 280px;
		}
		.partial-overflow {
		    overflow: auto;
		    max-height: 295px;
		}
		#modal-p {
    		font-size: 13px;
    	}
    	#md-border-line {
		    margin-top: 10px;
		    margin-bottom: 10px;
		}
		#md-qrcode-img-div img {
		    width: 100px;
		}
		#md-qrcode-title {
		    font-size: 10px;
		}
    }

	/*HEADER SECTION*/
	.dropdown-menu {
		border: unset;
		box-shadow: unset;
	}
	.hd-sm-logo {
		width: 40px;
		height: auto;
	}
	.notification-item:hover {
		background: #ffffff1a;
		transition: all 0.3s linear;
	}
	#notification-title {
		font-size: 14px;
		font-weight: 500;
		line-height: 1.4;
		margin-bottom: 3px;
	}
	#notification-p {
		font-size: 10px;
		line-height: 1.4;
		margin-bottom: 5px;
	}
	#notification-time {
		font-size: 11px;
		line-height: 1.4;
		margin-bottom: 0;
	}
	#notification-time i {
		margin-right: 8px;
	}
	#notification-btn-div {
		margin: 15px 0;
		text-align: center;
	}
	#hd-username {
		font-size: 12px;
		font-weight: 500;
		line-height: 1.4;
		margin-bottom: 0;
	}
	#hd-position {
		font-size: 11px;
		line-height: 1.4;
		margin-bottom: 0;
	}
	#hd-arrow-div img {
		width: 12px;
		height: auto;
	}
	@media (max-width: 991px){
		[data-layout=vertical] .horizontal-logo {
		    display: block;
		}
		#notification-title {
			font-size: 12px;
		}
		#notification-p {
			font-size: 8px;
		}
		#notification-time {
			font-size: 9px;
		}
		#notification-btn-div {
			text-align: left;
		}
		#notification-btn-div a {
			width: 100%;
		}
		.header-item {
			padding: 0;
		}
		.header-profile-user {
		    margin-right: 10px;
		}
		#hd-username {
			font-size: 10px;
		}
		#hd-position {
			font-size: 9px;
		}
		.notification-item {
    		padding: 0.75rem 0;
    	}
    	#hd-arrow-div img {
		    width: 10px;
		    margin-right: 8px;
		}
	}

	/*FOOTER SECTION*/
	#footer-section {
		right: 0;
	    bottom: 0;
	    left: 250px;
		padding-top: 0;
		position: absolute;
		padding-bottom: 1.5rem;
		background: transparent;
	}
	#footer-info-div {
		text-align: center;
	}
	#footer-p {
		font-size: 13px;
		line-height: 1.4;
		margin-bottom: 0;
	}
	@media (max-width: 991px){
		#footer-section {
			left: 0;
		    padding-bottom: 6.5rem;
		}
	}

	/*MOBILE MENU SECTION*/
	#mm-section {
		display: none;
	}
	@media (max-width: 991px){
		#mm-section {
		    left: 0;
		    right: 0;
		    bottom: 0;
		    z-index: 98;
		    display: flex;
			position: fixed;
			align-items: center;
			justify-content: center;
			backdrop-filter: blur(20px);
			border-top: 2px solid transparent;
    		border-image-slice: 1;
		}
		#mm-section ul {
			display: flex;
			padding: 0;
			margin: 0;
		}
		#mm-section li {
			z-index: 1;
			width: 60px;
			height: 60px;
			list-style: none;
			position: relative;
		}
		#mm-section ul li a {
			width: 100%;
			display: flex;
			font-weight: 500;
			position: relative;
			text-align: center;
			align-items: center;
			flex-direction: column;
			justify-content: center;
		}
		#mm-section ul li a #mm-icon {
			display: block;
			font-size: 1.5em;
			line-height: 60px;
			text-align: center;
			position: relative;
			transition: all 0.5s;
		}
		#mm-section li#active a #mm-icon {
			font-size: 28px;
			transform: translateY(-32px);
		}
		#mm-section li:hover a #mm-icon {
			transform: translateY(-10px);
		}
		#mm-section ul li a #mm-text {
			opacity: 0;
			font-weight: 400;
			font-size: 0.75em;
			position: absolute;
			transition: all 0.5s;
			letter-spacing: 0.05em;
			transform: translateY(20px);
		}
		#mm-section li#active a #mm-text, #mm-section li:hover a #mm-text {
			opacity: 1;
			transform: translateY(10px);
		}
		#mm-indicator {
			top: -50%;
			width: 60px;
			height: 60px;
			position: absolute;
			border-radius: 50%;
			transition: all 0.5s;
		}
		#mm-section li:nth-child(1)#active ~ #mm-indicator {
			transform: translateX(calc(60px * 0));
		}
		#mm-section li:nth-child(2)#active ~ #mm-indicator {
			transform: translateX(calc(60px * 1));
		}
		#mm-section li:nth-child(3)#active ~ #mm-indicator {
			transform: translateX(calc(60px * 2));
		}
		#mm-section li:nth-child(4)#active ~ #mm-indicator {
			transform: translateX(calc(60px * 3));
		}
		#mm-section li:nth-child(5)#active ~ #mm-indicator {
			transform: translateX(calc(60px * 4));
		}
	}
</style>
<script>
    function validateHP(myfield, e, dec)
    {
        var key;
        var keychar;
      
        if (window.event)
            key = window.event.keyCode;
        else if (e)
            key = e.which;
        else
            return true;
        keychar = String.fromCharCode(key);
          
        // control keys
        if ((key==null) || (key==0) || (key==8) || 
            (key==9) || (key==13) || (key==27) )
            return true;
          
        // numbers
        else if ((("0123456789-").indexOf(keychar) > -1))
            return true;
          
        // decimal point jump
        else if (dec && (keychar == "."))
        {
            myfield.form.elements[dec].focus();
            return false;
        }
        else
            return false;
    }
    function validateOTP(myfield, e, dec)
    {
        var key;
        var keychar;
      
        if (window.event)
            key = window.event.keyCode;
        else if (e)
            key = e.which;
        else
            return true;
        keychar = String.fromCharCode(key);
          
        // control keys
        if ((key==null) || (key==0) || (key==8) || 
            (key==9) || (key==13) || (key==27) )
            return true;
          
        // numbers
        else if ((("0123456789").indexOf(keychar) > -1))
            return true;
          
        // decimal point jump
        else if (dec && (keychar == "."))
        {
            myfield.form.elements[dec].focus();
            return false;
        }
        else
            return false;
    }
    function validateAmount(myfield, e, dec)
    {
        var key;
        var keychar;
      
        if (window.event)
            key = window.event.keyCode;
        else if (e)
            key = e.which;
        else
            return true;
        keychar = String.fromCharCode(key);
          
        // control keys
        if ((key==null) || (key==0) || (key==8) || 
            (key==9) || (key==13) || (key==27) )
            return true;
          
        // numbers
        else if ((("0123456789.").indexOf(keychar) > -1))
            return true;
          
        // decimal point jump
        else if (dec && (keychar == "."))
        {
            myfield.form.elements[dec].focus();
            return false;
        }
        else
            return false;
    }
</script>