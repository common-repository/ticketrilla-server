<?php
/*
Plugin Name: Ticketrilla: Server Plugin
Plugin URI: https://ticketrilla.com/
Author: Daniil Babkin
Description: Developer's plugin for support of WordPress products
Version: 1.1.1
Requires at least: 4.9.1
Requires PHP: 7.2
Text Domain: ttls_translate
*/

/*
TTL Server: https://support.ticketrilla.com
TTL Description: Support for "Ticketrilla: Server" - an innovative ticketing and licensing system.
TTL Slug: ticketrilla-server
*/

/*
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

	defined( 'ABSPATH' ) || exit;

	define( 'TTLS_URL', plugin_dir_url( __FILE__ ) );
	define( 'TTLS_PATH', plugin_dir_path( __FILE__ ) );
	define( 'TTLS_PLUGIN', plugin_basename( __FILE__ ) );
	define( 'TTLS_PLUGIN_VERSION', '1.1.1' );



	require_once 'includes/class-functions.php';
	require_once 'includes/class-init.php';

	add_action( 'plugins_loaded', 'ttls_load_languages' );
	function ttls_load_languages(){
		load_plugin_textdomain( 'ttls_translate', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
	}

	register_activation_hook( __FILE__, array( TTLS(), 'on_activation' ) );
	register_deactivation_hook( __FILE__, array( TTLS(), 'on_deactivation' ) );