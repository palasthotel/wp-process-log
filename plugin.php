<?php

/**
 * Plugin Name:       Process logs - DEV
 * Description:       Dev inc file
 * Version:           X.X.X
 * Requires at least: X.X
 * Tested up to:      X.X.X
 * Author:            Palasthotel <webmaster@palasthotel.de>
 * Author URI:        https://palasthotel.de
 * Domain Path:       /public/languages
 */

defined( 'ABSPATH' ) || exit;

use Palasthotel\ProcessLog\Plugin;

include dirname( __FILE__ ) . "/public/plugin.php";

register_activation_hook(__FILE__, function($multisite){
	Plugin::instance()->onActivation($multisite);
});

register_deactivation_hook(__FILE__, function($multisite){
	Plugin::instance()->onDeactivation($multisite);
});
