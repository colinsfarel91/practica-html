<?php

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

if( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'smile-mailer-integrations' ) {
	require_once( 'integrations.php' );
} else if( isset( $_GET[ 'view' ] ) && $_GET[ 'view' ] == 'modules' ) {
	require_once( CP_BASE_DIR.'/admin/modules.php' );
} else if( isset( $_GET['view'] ) && $_GET['view'] ==  'settings' ) {
	require_once( CP_BASE_DIR.'/admin/settings.php' );
} else if( isset( $_GET['view'] ) && $_GET['view'] ==  'cp_import' ) {
	require_once( CP_BASE_DIR.'/admin/cp_import.php' );
} else if( isset( $_GET['view'] ) && $_GET['view'] ==  'registration' ) {
	require_once( CP_BASE_DIR.'/admin/registration.php' );
	//require_once( CP_BASE_DIR.'/admin/cp_registration.php' );
} else if( isset( $_GET['view'] ) && $_GET['view'] ==  'debug' ) {
	require_once( CP_BASE_DIR.'/admin/debug.php' );
} else if( isset( $_GET['view'] ) && $_GET['view'] ==  'knowledge_base' ) {
	require_once( CP_BASE_DIR.'/admin/knowledge_base.php' );
} else {
	require_once(CP_BASE_DIR.'/admin/get_started.php');
}
