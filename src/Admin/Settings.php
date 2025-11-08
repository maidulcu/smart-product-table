<?php
namespace SmartTable\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_settings_page() {
        add_options_page(
            __( 'Smart Product Table Settings', 'smart-product-table' ),
            __( 'Smart Product Table', 'smart-product-table' ),
            'manage_options',
            'smarttable-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'smarttable_settings', 'smarttable_posts_per_page', [
            'type' => 'integer',
            'default' => 10,
            'sanitize_callback' => [ $this, 'sanitize_posts_per_page' ]
        ] );

        add_settings_section(
            'smarttable_general',
            __( 'General Settings', 'smart-product-table' ),
            null,
            'smarttable-settings'
        );

        add_settings_field(
            'smarttable_posts_per_page',
            __( 'Posts Per Page', 'smart-product-table' ),
            [ $this, 'render_posts_per_page_field' ],
            'smarttable-settings',
            'smarttable_general'
        );
    }

    public function sanitize_posts_per_page( $value ) {
        return max( 1, min( 100, (int) $value ) );
    }

    public function render_posts_per_page_field() {
        $value = get_option( 'smarttable_posts_per_page', 10 );
        echo '<input type="number" name="smarttable_posts_per_page" value="' . esc_attr( $value ) . '" min="1" max="100" />';
        echo '<p class="description">' . __( 'Number of products to show per page (1-100)', 'smart-product-table' ) . '</p>';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'smarttable_settings' );
                do_settings_sections( 'smarttable-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function get_posts_per_page() {
        return get_option( 'smarttable_posts_per_page', 10 );
    }
}