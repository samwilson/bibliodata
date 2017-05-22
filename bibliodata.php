<?php
/*
Plugin Name: Bibliodata
Plugin URI:  https://samwilson.id.au/plugins/bibliodata
Description: 
Version:     0.1.0
Author:      samwilson
Author URI:  https://samwilson.id.au
License:     GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: bibliodata
Domain Path: /languages
*/

require_once __DIR__ . '/vendor/autoload.php';

use Samwilson\Bibliodata\Controllers\ControllerBase;
use Stash\Driver\FileSystem;
use Stash\Pool;

// Route POST requests.
add_action( 'admin_init', function() {
	// See if this is a Bibliodata page that's being POSTed.
	if (isset($_POST['page']) && substr($_POST['page'], 0, 10) == 'bibliodata') {
		// Extract the controller name from the page name.
		$controllerName = substr($_POST['page'], 11);
		bibliodata_router( $controllerName );
	}
} );

// Route GET requests (via menu pages).
add_action( 'admin_menu', function() {
	add_submenu_page( 'tools.php', 'Bibliodata', 'Bibliodata', 'edit_posts', 'bibliodata-tools', function () {
		bibliodata_router('Tools');
	} );
	add_submenu_page( 'options-general.php', 'Bibliodata', 'Bibliodata', 'edit_posts', 'bibliodata-options', function () {
		bibliodata_router('Options');
	});
});

/**
 * Dispatch to the controller.
 *
 * @param string $controllerName
 */
function bibliodata_router($controllerName) {
	// Cache.
	$driver = new FileSystem();
	$cachePool = new Pool($driver);

	// Controller.
	$controllerClassName = '\\Samwilson\\Bibliodata\\Controllers\\' . ucfirst( $controllerName ) . 'Controller';
	/** @var ControllerBase $controller */
	$controller = new $controllerClassName();
	$action = ( isset( $_REQUEST['action'] ) && is_callable( [ $controller, $_REQUEST['action'] ] ) )
		? $_REQUEST['action']
		: 'index';
	$controller->setCache($cachePool);

	// Dispatch.
	$controller->$action();
}
