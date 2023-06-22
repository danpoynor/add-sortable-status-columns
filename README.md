# Add Sortable Status Columns

WordPress plugin that adds a "Status" column to admin list tables and allows applying CSS styling each status type to visually distinguish them.

Use `Settings` > `Add Sortable Status Columns` to define CSS styles for each status type.

## Features

- Status column automatically added to all public post type, including custom post types, upon activation.
- When viewing a list table, the Status column is sortable in both ascending and descending directions.
- Settings interface includes text areas to add CSS rules for styling each of the post types.
- Preset styles included for Default (no styles), Outline, Solid, and Only Default Flagged as example style sets which can be selected and saved, or further edited for your specific needs.

## Installation

Follow these steps to install the Additional Image Sizes Manager plugin:

1. Download the plugin ZIP file from the [GitHub repository](https://github.com/danpoynor/add-sortable-status-columns).
2. Log in to your WordPress administration area.
3. Navigate to Plugins > Add New.
4. Click the Upload Plugin button at the top of the page.
5. Choose the downloaded ZIP file and click Install Now.
6. After installation, click the Activate Plugin button.

## Usage

After activating the plugin, you'll see the Status column on the right in list tables that appear on All Posts, All Pages lists, and any custom post type pages.

On the page under Settings > Add Sortable Status Columns you can define the CSS styles for each post type. The built-in styles are Default, Outline, Solid, and Flag Drafts Only. You are also able to customize the styles to change looks or colors and click Save Status Styles to update the look.

## Uninstall

If you choose to Deactivate and Delete the plugin, the `uninstall.php` script will run and the Add Sortable Status Column type styles option (`assc_type_styles`) will be removed from the database and any saved custom styles will be lost.

## Screenshots

Add Sortable Status Columns Settings page with CSS style editing fields for each Status type

![01-add-sortable-status-columns-wp-plugin-settings-screen](https://github.com/danpoynor/add-sortable-status-columns/assets/764270/16e5fcf2-c5b1-489d-838a-85fb76074f0b)

Example list page showing Default styles used for Status types in right column

![04-add-sortable-status-columns-wp-plugin-active-with-default-styles](https://github.com/danpoynor/add-sortable-status-columns/assets/764270/61151ea7-ae80-468f-9805-17f6cbe37aa5)

Example list page showing Outline styles used for Status types

![02-add-sortable-status-columns-wp-plugin-active-with-outline-styles](https://github.com/danpoynor/add-sortable-status-columns/assets/764270/3326b2f0-431c-498e-a22f-b54e32c30f50)

Example list page showing Solid styles used for Status types

![03-add-sortable-status-columns-wp-plugin-active-with-solid-styles](https://github.com/danpoynor/add-sortable-status-columns/assets/764270/07b4484e-efdd-4d02-a568-7e7ecc4fd63f)

## Known Bugs

None currently.

## Potential To-Do List

- Allow selecting which post types the Status column should appear on instead of defaulting to all.
- Add example style set for Accessible chips for low-vision or color-blind users.
- Add setting for column order for Status should appear in.
- Add support for other languages.
- Test with a bunch of other plugins to check for conflicts.
- Add unit tests.
- Submit to <https://wordpress.org/plugins/>
