<?php
if (!defined('ABSPATH')) {
    // If this file is called directly, abort
    exit;
}

// Register the admin menu
function assc_admin_menu()
{
    add_options_page(
        'Add Sortable Status Columns Settings',
        'Add Sortable Status Columns',
        'manage_options',
        'add_sortable_status_columns',
        'assc_settings_page'
    );
}
add_action('admin_menu', 'assc_admin_menu');

// Create the plugin settings page
function assc_settings_page()
{
    // Check user capability
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check if the form is submitted and nonce is valid
    if (isset($_POST['assc_settings_nonce']) && wp_verify_nonce($_POST['assc_settings_nonce'], 'assc_settings')) {
        // Sanitize and save status styles if form submitted
        if (isset($_POST['assc_type_styles'])) {
            $status_styles = array_map('sanitize_textarea_field', $_POST['assc_type_styles']);
            update_option('assc_type_styles', $status_styles);
            echo '<div class="notice notice-success is-dismissible"><p>Status styles saved.</p></div>';
        }
    }

    // Retrieve all privacy-related statuses
    $privacy_statuses = _wp_privacy_statuses();

    // Retrieve all non-internal post statuses
    $post_stati = get_post_stati(array('internal' => false), 'objects');

    // Manually add the 'trash' status
    $trash_status = get_post_status_object('trash');
    if (!is_null($trash_status)) {
        $post_stati['trash'] = $trash_status;
    }

    // Array to hold filtered status objects. Keys are status names.
    $filtered_status_objects = array();

    // Loop through the post statuses, filter out privacy-related statuses
    foreach ($post_stati as $status) {
        if (!in_array($status->name, array_keys($privacy_statuses))) {
            // Add the entire status object to our array of filtered status objects, using status name as key
            $filtered_status_objects[$status->name] = $status;
        }
    }

    // Remove the 'Inactive' status object from the array of filtered status objects since it doesn't seem to apply to posts.
    // We're checking against the label property because the name and label might not match exactly.
    foreach ($filtered_status_objects as $key => $status) {
        if ($status->label === 'Inactive') {
            unset($filtered_status_objects[$key]);
        }
    }

    // Retrieve status styles options
    $status_styles = get_option('assc_type_styles', array());

?>
    <div class="wrap">
        <h1>Add Sortable Status Columns Settings</h1>

        <h2>Status Styles</h2>
        <ul class="assc-status-styles-list" id="assc-status-styles-list" style="list-style-type:none;padding-left:0;display:flex;align-items:center;gap:.5rem;">
            <li>Select a defined status type style set to start with:</li>
            <li><button class="button action" data-status-type="default">Default</button></li>
            <li><button class="button action" data-status-type="outline">Outline</button></li>
            <li><button class="button action" data-status-type="solid">Solid</button></li>
            <li><button class="button action" data-status-type="flag_drafts_only">Flag Drafts Only</button></li>
        </ul>
        <p>Or add custom CSS styles for each post status such as <code>color: #ff0000; font-weight: bold;</code> to help visually distinguish between status types.</p>
        <form method="post" action="">
            <?php wp_nonce_field('assc_settings', 'assc_settings_nonce'); ?>
            <table class="form-table">
                <?php foreach ($filtered_status_objects as $status) : ?>
                    <tr>
                        <th scope="row">
                            <label for="assc_type_styles_<?php echo esc_attr($status->name); ?>"><?php echo esc_html($status->label); ?>:</label>
                        </th>
                        <td>
                            <textarea 
                                id="assc_type_styles_<?php echo esc_attr($status->name); ?>" 
                                name="assc_type_styles[<?php echo esc_attr($status->name); ?>]" 
                                rows="5" 
                                cols="50" 
                                maxlength="512" 
                                autocomplete="off"
                                placeholder="Enter CSS styles"><?php echo isset($status_styles[$status->name]) ? esc_html($status_styles[$status->name]) : ''; ?></textarea>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button('Save Status Styles'); ?>
        </form>
    </div>
<?php
}

function assc_on_admin_init()
{
    $public_post_types = get_post_types(array('public' => true), 'names');

    foreach ($public_post_types as $post_type) {
        add_filter("manage_{$post_type}_posts_columns", 'assc_add_status_column');
        add_filter("manage_edit-{$post_type}_sortable_columns", 'assc_sortable_columns');
        add_action("manage_{$post_type}_posts_custom_column", 'assc_populate_status_column', 10, 2);
    }
}
add_action('admin_init', 'assc_on_admin_init');

// Modify the columns for the "Status" column
function assc_add_status_column($columns)
{
    $columns['status'] = 'Status';
    return $columns;
}

// Make the "Status" column sortable
function assc_sortable_columns($columns)
{
    $columns['status'] = 'status';
    return $columns;
}

// Modify the posts_clauses to allow ordering by post_status
function assc_orderby_status($pieces, $query)
{
    global $wpdb;

    if ($query->get('orderby') == 'status') {
        $pieces['join'] .= " LEFT JOIN {$wpdb->posts} as alias ON {$wpdb->posts}.ID = alias.ID ";
        $pieces['orderby'] = "alias.post_status " . $query->get('order');
    }

    return $pieces;
}
add_filter('posts_clauses', 'assc_orderby_status', 10, 2);


// Populate the "Status" column with post status and apply styles
function assc_populate_status_column($column, $post_id)
{
    if ($column === 'status') {
        $post_status = get_post_status($post_id);
        $status_label = '';

        switch ($post_status) {
            case 'publish':
                $status_label = 'Published';
                break;
            case 'future':
                $status_label = 'Scheduled';
                break;
            case 'draft':
                $status_label = 'Draft';
                break;
            case 'pending':
                $status_label = 'Pending Review';
                break;
            case 'private':
                $status_label = 'Private';
                break;
            case 'trash':
                $status_label = 'Trash';
                break;
            default:
                $status_label = $post_status;
                break;
        }

        // Get the user-defined styles for post status
        $status_styles = get_option('assc_type_styles', array());

        // Apply the styles for the current status, if available
        $style = isset($status_styles[$post_status]) ? $status_styles[$post_status] : '';

        echo '<span style="' . $style . '">' . $status_label . '</span>';
    }
}

// Set the custom order for statuses
function assc_set_status_order()
{
    $status_order = array(
        'draft' => 1,
        'pending' => 2,
        'private' => 3,
        'publish' => 4,
        'future' => 5,
        'trash' => 6,
        'auto-draft' => 7,
        'inherit' => 8,
    );

    foreach ($status_order as $status => $order) {
        assc_update_post_meta_by_status($status, $order);
    }
}
add_action('admin_init', 'assc_set_status_order');

// Update the post meta with custom status order
function assc_update_post_meta_by_status($status, $order)
{
    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'custom_post_type',
        'post_status' => $status,
    );

    $posts = get_posts($args);

    foreach ($posts as $post) {
        update_post_meta($post->ID, '_sortable_status_order', $order);
    }
}

// Add JavaScript to the admin footer for switching between status styles
function assc_add_status_styles_js()
{
    $status_styles = get_option('assc_type_styles', array());
    $status_styles_json = json_encode($status_styles);

?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var styleSets = {
                default: {
                    publish: '',
                    future: '',
                    draft: '',
                    pending: '',
                    private: '',
                    trash: ''
                },
                outline: {
                    publish: 'color:#369132;\nbackground-color:#e2ffe0;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;border:1px solid #82e87d;',
                    future: 'color:#c20ac2;\nbackground-color:#fce6fc;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;border:1px solid #fba7fb;',
                    draft: 'color:#1d5cc9;\nbackground-color:#d6f4fe;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;border:1px solid #8ab5ff;',
                    pending: 'color:#993000;\nbackground-color:#fffbdc;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase; border:1px solid #ffdd33;',
                    private: 'color:#cf171d;\nbackground-color:#fee7e8;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;border:1px solid #f7babc;',
                    trash: 'color:#666;\nbackground-color:#eee;\npadding:.25rem .5rem;\nborder: 1px solid #ccc;\nborder-radius:1rem; text-transform:uppercase;'
                },
                solid: {
                    publish: 'color:#666;\nbackground-color:#fff;\npadding:.25rem .5rem;\nborder: 1px solid #ccc;\nborder-radius:1rem; text-transform:uppercase;',
                    future: 'color:#fff;\nbackground-color:#339900;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;',
                    draft: 'color:#fff;\nbackground-color:#0000ff;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;',
                    pending: 'color:#000;\nbackground-color:#ffcc33;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;',
                    private: 'color:#fff;\nbackground-color:red;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;',
                    trash: 'color:#fff;\nbackground-color:#000;\npadding:.25rem .5rem;\nborder-radius:1rem; text-transform:uppercase;'
                },
                flag_drafts_only: {
                    publish: '',
                    future: '',
                    draft: 'color:#fff;\nbackground-color:red;\npadding:.25rem .5rem;\nborder-radius:1rem;\ntext-transform:uppercase;',
                    pending: '',
                    private: '',
                    trash: ''
                }
            };
            document.body.addEventListener('click', function(e) {
                var target = e.target;
                if (target.matches('#assc-status-styles-list button')) {
                    e.preventDefault();
                    console.log("hit");
                    var styleType = target.getAttribute('data-status-type');
                    if (styleSets[styleType]) {
                        Object.keys(styleSets[styleType]).forEach(function(key) {
                            var textarea = document.getElementById('assc_type_styles_' + key);
                            if (textarea) {
                                textarea.value = styleSets[styleType][key];
                            }
                        });
                    }
                }
            })
        });
    </script>
<?php
}
add_action('admin_footer', 'assc_add_status_styles_js');
