<?php 

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'new-list' ) {
	require_once( CP_BASE_DIR.'/admin/contacts/views/new-list.php' );
} elseif( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'contacts' ) {
	require_once(  CP_BASE_DIR.'/admin/contacts/views/contacts.php' );
} elseif( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'analytics' ) {
	require_once(  CP_BASE_DIR.'/admin/contacts/views/analytics.php' );
} else if( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'contact-details' ) {
	require_once(  CP_BASE_DIR.'/admin/contacts/views/contact-details.php' );
} else {
	require_once(  CP_BASE_DIR.'/admin/contacts/views/dashboard.php' );
}