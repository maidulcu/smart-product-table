<?php
namespace SmartTable\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class LayoutBuilderMetabox {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'register_metabox']);
        add_action('save_post', [$this, 'save_layout'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Ensure metaboxes work with Gutenberg
        add_filter('use_block_editor_for_post_type', [$this, 'enable_gutenberg_for_post_type'], 10, 2);
    }

    public function enable_gutenberg_for_post_type($use_block_editor, $post_type) {
        if ($post_type === 'smarttable_product') {
            return true;
        }
        return $use_block_editor;
    }

    public function register_metabox() {
        $screen = get_current_screen();
        
        // Only add metaboxes for smarttable_product post type
        if (!$screen || $screen->post_type !== 'smarttable_product') {
            return;
        }

        add_meta_box(
            'smarttable_layout_builder',
            __('Table Layout Builder', 'smart-product-table'),
            [$this, 'render_metabox'],
            'smarttable_product',
            'normal',
            'high',
            null,
            [
                '__block_editor_compatible_meta_box' => true,
                '__back_compat_meta_box' => false,
            ]
        );

        add_meta_box(
            'smarttable_shortcode_display',
            __('Shortcode', 'smart-product-table'),
            [$this, 'render_shortcode_metabox'],
            'smarttable_product',
            'side',
            'high',
            null,
            [
                '__block_editor_compatible_meta_box' => true,
                '__back_compat_meta_box' => false,
            ]
        );
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'post.php' && $hook !== 'post-new.php') return;

        wp_enqueue_script(
        'sortable-js',
        'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js',
        [],
        null,
        true
        );
        wp_enqueue_style('smarttable-layout-style', plugins_url('/assets/admin/css/layout-builder.css', SMARTTABLE_PLUGIN_FILE));
        wp_enqueue_style('wp-components'); // Ensures admin layout consistency
        wp_enqueue_script('smarttable-layout-script', plugins_url('/assets/admin/js/layout-builder.js', SMARTTABLE_PLUGIN_FILE), ['jquery'], null, true);
    }

    public function render_metabox($post) {
        $saved_layout = get_post_meta($post->ID, '_smarttable_column_layout', true);
        $layout_data = $saved_layout ? json_decode($saved_layout, true) : [];

        ?>
        <div class="smarttable-metabox-tabs">
            <h2 class="nav-tab-wrapper">
                <a href="#smarttable-tab-layout" class="nav-tab nav-tab-active">Layout</a>
                <a href="#smarttable-tab-filters" class="nav-tab">Filters</a>
                <a href="#smarttable-tab-display" class="nav-tab">Display Options</a>
                <a href="#smarttable-tab-style" class="nav-tab">Style</a>
            </h2>

            <div id="smarttable-tab-layout" class="smarttable-tab-content active">
                <div class="postbox smarttable-layout-builder-wrap"><div class="inside">
                    <?php include SMARTTABLE_PLUGIN_DIR . 'admin/views/layout-builder-metabox.php'; ?>
                </div></div>
            </div>

            <div id="smarttable-tab-filters" class="smarttable-tab-content" style="display:none;">
                <div class="postbox"><div class="inside">
                    <?php include SMARTTABLE_PLUGIN_DIR . 'admin/views/layout-filters.php'; ?>
                </div></div>
            </div>

            <div id="smarttable-tab-display" class="smarttable-tab-content" style="display:none;">
                <div class="postbox"><div class="inside">
                    <?php include SMARTTABLE_PLUGIN_DIR . 'admin/views/layout-display-options.php'; ?>
                </div></div>
            </div>

            <div id="smarttable-tab-style" class="smarttable-tab-content" style="display:none;">
                <div class="postbox"><div class="inside">
                    <?php include SMARTTABLE_PLUGIN_DIR . 'admin/views/layout-style-options.php'; ?>
                </div></div>
            </div>
        </div>
        <script>
        (function($){
            $('.smarttable-metabox-tabs .nav-tab').on('click', function(e){
                e.preventDefault();
                var target = $(this).attr('href');
                var $targetContent = $(target);
                
                // Remove active classes
                $('.smarttable-metabox-tabs .nav-tab').removeClass('nav-tab-active');
                $('.smarttable-metabox-tabs .smarttable-tab-content').removeClass('active').hide();
                
                // Add active class to clicked tab
                $(this).addClass('nav-tab-active');
                
                // Show target content with animation
                setTimeout(function() {
                    $targetContent.show().addClass('active');
                }, 50);
            });
            
            // Initialize first tab as active
            $('.smarttable-metabox-tabs .smarttable-tab-content:first').addClass('active');
        })(jQuery);
        </script>
        <?php
    }

    public function render_shortcode_metabox($post) {
        $shortcode = sprintf('[smarttable id="%d"]', $post->ID);
        echo '<div style="display:flex;gap:10px;align-items:center;">';
        echo '<input id="smarttable-shortcode-field" type="text" readonly class="widefat" value="' . esc_attr($shortcode) . '" style="flex:1;" />';
        echo '<button type="button" class="button" id="smarttable-copy-shortcode-btn">Copy</button>';
        echo '</div>';
        echo '<p class="description">Click copy to use this shortcode anywhere on your site.</p>';
    }

    public function save_layout($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type !== 'smarttable_product') return;
        
        // Debug: Log what's being saved
        if (current_user_can('manage_options')) {
            error_log('Saving post ' . $post_id . ' with min_price: ' . ($_POST['smarttable_min_price'] ?? 'not set'));
            error_log('Saving post ' . $post_id . ' with max_price: ' . ($_POST['smarttable_max_price'] ?? 'not set'));
        }

        $layout_json = isset($_POST['smarttable_column_layout']) ? wp_unslash($_POST['smarttable_column_layout']) : '';
        update_post_meta($post_id, '_smarttable_column_layout', $layout_json);

        // Save product query filter fields
        $categories = isset($_POST['smarttable_filter_categories']) && is_array($_POST['smarttable_filter_categories']) ? array_map('sanitize_text_field', $_POST['smarttable_filter_categories']) : [];
        $tags       = isset($_POST['smarttable_filter_tags']) && is_array($_POST['smarttable_filter_tags']) ? array_map('sanitize_text_field', $_POST['smarttable_filter_tags']) : [];

        update_post_meta($post_id, '_smarttable_filter_categories', implode(',', $categories));
        update_post_meta($post_id, '_smarttable_filter_tags', implode(',', $tags));
        update_post_meta($post_id, '_smarttable_include_ids', sanitize_text_field($_POST['smarttable_include_ids'] ?? ''));
        update_post_meta($post_id, '_smarttable_exclude_ids', sanitize_text_field($_POST['smarttable_exclude_ids'] ?? ''));

        update_post_meta($post_id, '_smarttable_enable_pagination', isset($_POST['smarttable_enable_pagination']) ? '1' : '');
        update_post_meta($post_id, '_smarttable_products_per_page', absint($_POST['smarttable_products_per_page'] ?? 10));
        update_post_meta($post_id, '_smarttable_default_sort', sanitize_text_field($_POST['smarttable_default_sort'] ?? ''));

        update_post_meta($post_id, '_smarttable_tax_query_relation', in_array($_POST['smarttable_tax_query_relation'] ?? '', ['AND', 'OR'], true) ? $_POST['smarttable_tax_query_relation'] : 'AND');

        // Save style settings
        $design_style = sanitize_text_field($_POST['smarttable_design_style'] ?? 'default');
        update_post_meta($post_id, '_smarttable_design_style', $design_style);
        
        // Save display options
        update_post_meta($post_id, '_smarttable_enable_pagination', isset($_POST['smarttable_enable_pagination']) ? '1' : '0');
        update_post_meta($post_id, '_smarttable_show_search', isset($_POST['smarttable_show_search']) ? '1' : '0');
        update_post_meta($post_id, '_smarttable_show_filters', isset($_POST['smarttable_show_filters']) ? '1' : '0');
        update_post_meta($post_id, '_smarttable_show_bulk_cart', isset($_POST['smarttable_show_bulk_cart']) ? '1' : '0');

        // Save advanced filter settings
        update_post_meta($post_id, '_smarttable_product_type', isset($_POST['smarttable_product_type']) ? implode(',', array_map('sanitize_text_field', $_POST['smarttable_product_type'])) : '');
        update_post_meta($post_id, '_smarttable_featured_only', sanitize_text_field($_POST['smarttable_featured_only'] ?? ''));
        update_post_meta($post_id, '_smarttable_min_price', floatval($_POST['smarttable_min_price'] ?? 0));
        update_post_meta($post_id, '_smarttable_max_price', floatval($_POST['smarttable_max_price'] ?? 0));
        update_post_meta($post_id, '_smarttable_stock_status', isset($_POST['smarttable_stock_status']) ? implode(',', array_map('sanitize_text_field', $_POST['smarttable_stock_status'])) : '');
        update_post_meta($post_id, '_smarttable_on_sale', sanitize_text_field($_POST['smarttable_on_sale'] ?? ''));
        update_post_meta($post_id, '_smarttable_date_from', sanitize_text_field($_POST['smarttable_date_from'] ?? ''));
        update_post_meta($post_id, '_smarttable_date_to', sanitize_text_field($_POST['smarttable_date_to'] ?? ''));
        update_post_meta($post_id, '_smarttable_product_limit', absint($_POST['smarttable_product_limit'] ?? 0));
    }
}