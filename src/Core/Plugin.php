<?php
namespace SmartTable\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class Plugin {

    public function init() {
        // Hook to run initialization at WP init
        add_action( 'init', [ $this, 'initialize' ] );

        // Frontend assets
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Admin assets
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );
    }

    public function initialize() {
        
        // Debug
        // if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        //     error_log( '[SmartTable] Plugin::initialize running' );
        // }

        // Ensure autoload or require files if not using composer
        // If you use composer PSR-4 autoload, vendor/autoload.php should already be included in main plugin file.
        // Fallback: require ShortcodeManager manually if class doesn't exist.
        if ( ! class_exists( '\SmartTable\Core\ShortcodeManager' ) ) {
            $possible = SMARTTABLE_PLUGIN_DIR . 'src/Core/ShortcodeManager.php';
            if ( file_exists( $possible ) ) {
                require_once $possible;
            }
        }

        // Initialize ShortcodeManager (singleton)
        if ( class_exists( '\SmartTable\Core\ShortcodeManager' ) && method_exists( '\SmartTable\Core\ShortcodeManager', 'get_instance' ) ) {
            \SmartTable\Core\ShortcodeManager::get_instance()->init();
            // if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            //     error_log( '[SmartTable] ShortcodeManager initialized' );
            // }
        } else {
            error_log( '[SmartTable][WARN] ShortcodeManager not found or missing get_instance()' );
        }

        // Initialize ColumnManager
        if ( class_exists( '\SmartTable\Core\ColumnManager' ) ) {
            \SmartTable\Core\ColumnManager::instance()->boot();
        }

        // Initialize TableShortcode if exists
        if ( class_exists( '\SmartTable\Shortcode\TableShortcode' ) ) {
            ( new \SmartTable\Shortcode\TableShortcode() )->init();
        }

        // Instantiate CPT class if exists (its constructor can add CPT hooks)
          // Instantiate CPT (constructor hooks registration)
       $product = new \SmartTable\CPT\Post_Type_Product(); // constructor hooks it automatically
        $product->register_post_type();


        // Ajax handler (if exists)
        if ( class_exists( '\SmartTable\Ajax\TableAjaxHandler' ) ) {
            new \SmartTable\Ajax\TableAjaxHandler();
        }
        
        // Pagination AJAX handler
        if ( class_exists( '\SmartTable\Ajax\PaginationHandler' ) ) {
            new \SmartTable\Ajax\PaginationHandler();
        }
        
        // Bulk Cart AJAX handler
        if ( class_exists( '\SmartTable\Ajax\BulkCartHandler' ) ) {
            new \SmartTable\Ajax\BulkCartHandler();
        }
        
        // Filter AJAX handler
        if ( class_exists( '\SmartTable\Ajax\FilterHandler' ) ) {
            new \SmartTable\Ajax\FilterHandler();
        }
        
        // Initialize TableHooks (no instantiation needed for static methods)
        // TableHooks functions are available globally

         if ( class_exists( '\SmartTable\Admin\LayoutBuilderMetabox' ) ) {
                new \SmartTable\Admin\LayoutBuilderMetabox();
         }
         
         // Admin Settings
         if ( class_exists( '\SmartTable\Admin\Settings' ) ) {
             new \SmartTable\Admin\Settings();
         }
        
    }

    public function enqueue_scripts() {
        // enqueue frontend scripts/styles here
        if ( defined( 'SMARTTABLE_PLUGIN_URL' ) ) {
            wp_enqueue_style( 'smarttable-css', SMARTTABLE_PLUGIN_URL . 'assets/css/smarttable.css', [], SMARTTABLE_VERSION );
            wp_enqueue_script( 'smarttable-js', SMARTTABLE_PLUGIN_URL . 'assets/js/smarttable.js', [ 'jquery' ], SMARTTABLE_VERSION, true );
            wp_localize_script( 'smarttable-js', 'smarttable_ajax_object', [ 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'smarttable_nonce' )
            ] );
        }
    }

    public function admin_enqueue( $hook ) {
        // Only enqueue admin JS/CSS on post edit screens if needed
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( $screen && $screen->post_type !== 'smarttable_product' ) {
            return;
        }

        if ( defined( 'SMARTTABLE_PLUGIN_URL' ) ) {
            //wp_enqueue_script( 'smarttable-admin-js', SMARTTABLE_PLUGIN_URL . 'assets/js/admin-metaboxes.js', [ 'jquery' ], SMARTTABLE_VERSION, true );
            wp_enqueue_script( 'smarttable-admin-js', SMARTTABLE_PLUGIN_URL . 'assets/admin/js/smarttable-tabs.js', [ 'jquery' ], SMARTTABLE_VERSION, true );
            //wp_enqueue_style( 'smarttable-admin-css', SMARTTABLE_PLUGIN_URL . 'assets/css/admin.css', [], SMARTTABLE_VERSION );
            wp_enqueue_style(
                'smarttable-admin-css',
                SMARTTABLE_PLUGIN_URL . 'assets/admin/css/smarttable-admin.css',
                [],
                SMARTTABLE_VERSION
            );  
            wp_enqueue_style(
            'choices-css',
            'https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css',
            [],
            null
            );
            wp_enqueue_script(
                'choices-js',
                'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js',
                [],
                null,
                true
            );
    wp_add_inline_script('choices-js', "
        document.addEventListener('DOMContentLoaded', function () {
            const cats = document.querySelector('#smarttable_filter_categories');
            if(cats){
                new Choices(cats, {
                    removeItemButton: true,
                    searchPlaceholderValue: 'Search categories...',
                    placeholder: true,
                    placeholderValue: 'Select categories',
                });
            }

            const tags = document.querySelector('#smarttable_filter_tags');
            if(tags){
                new Choices(tags, {
                    removeItemButton: true,
                    searchPlaceholderValue: 'Search tags...',
                    placeholder: true,
                    placeholderValue: 'Select tags',
                });
            }
        });
    ");
        }
    }
}