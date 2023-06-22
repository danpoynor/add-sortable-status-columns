<?php

/**
 * Add Sortable Status Columns
 *
 * @package           Add Sortable Status Columns
 * @author            Dan Poynor
 * @link              https://danpoynor.com
 * @version           1.0.0
 * @copyright         Dan Poynor
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Add Sortable Status Columns
 * Description: Adds a "Status" column to public post types in the admin list tables and allows styling of post status types.
 * Version: 1.0.0
 * Author: Dan Poynor
 * Author URI: https://danpoynor.com
 * Text Domain: add-sortable-status-columns
 */

// Prevent 'headers already sent' errors when redirecting after updates
ob_start();

if (!defined('ABSPATH')) {
    // If this file is called directly, abort
    exit;
}

if (is_admin()) {
    // We are in admin mode
    require_once __DIR__ . '/admin/add-sortable-status-columns-admin.php';

    // Add settings link on plugin page
    function assc_settings_link($links, $file)
    {
        // Check if the current plugin is the one you want
        if ($file === plugin_basename(__FILE__)) {
            $settings_link = '<a href="options-general.php?page=add_sortable_status_columns">' . __('Settings', 'add-sortable-status-columns') . '</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }
    add_filter('plugin_action_links', 'assc_settings_link', 10, 2);
}
