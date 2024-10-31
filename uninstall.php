<?php
/**
 * Picasa Album Uploader Uninstall processing
 *
 * Called by Wordpress to uninstall a plugin
 *
 * @package Picasa Album Uploader
 * @author Kenneth J. Brucker <ken@pumastudios.com>
 * @copyright 2011 Kenneth J. Brucker (email: ken@pumastudios.com)
 * 
 * This file is part of Picasa Album Uploader, a plugin for Wordpress.
 *
 * Picasa Album Uploader is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Picasa Album Uploader is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Picasa Album Uploader.  If not, see <http://www.gnu.org/licenses/>.
 **/

// Make sure this is being called in the context of a real uninstall request
if (!defined('WP_UNINSTALL_PLUGIN')) {
	header('Status: 403 Forbidden');
  header('HTTP/1.1 403 Forbidden');
  exit();
}

if (is_admin() && current_user_can('manage_options') && current_user_can('install_plugins')) {
	require_once(WP_PLUGIN_DIR . '/picasa-album-uploader/admin/options.php');
	
	$options = new picasa_album_uploader_options();
	$options->uninstall();
	unset($options);
} else {
	wp_die(__('You do not have authorization to run the uninstall script for this plugin.', 'picasa-album-uploader'));
}

?>