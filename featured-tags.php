<?php
/*
 * Plugin Name: Featured Tags
 * Description: A plugin to add a Tumblr-like queue feature for WordPress posts.
 * Version: 0.2.1
 * Author: Automattic
 * Text Domain: featured-tags
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'FEATURED_TAGS_VERSION', '0.2.1' );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-featured-tags.php';

use Featured_Tags\Featured_Tags;

$featured_tags = new Featured_Tags();
$featured_tags->run();
