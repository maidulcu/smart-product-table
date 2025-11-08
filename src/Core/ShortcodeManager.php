<?php
namespace SmartTable\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ShortcodeManager
 *
 * - Generates a unique shortcode/token on save of sp_product
 * - Displays generated shortcode in a metabox (copy + regenerate)
 * - Registers shortcode handler [spt_table id="..."]
 * - Prevents default redirect to post.php after saving sp_product (redirects back to referer or post list)
 *
 * Save as: src/Core/ShortcodeManager.php
 */
class ShortcodeManager {

    const META_KEY_SHORTCODE = '_spt_shortcode';
    const META_KEY_TOKEN     = '_spt_shortcode_token';
    const REGEN_ACTION       = 'spt_regenerate_shortcode'; // admin_post action

    // singleton
    protected static $instance = null;

    protected $post_type = 'sp_product';

    /**
     * Return singleton instance
     *
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Protected constructor to enforce singleton.
     */
    protected function __construct() {
        // keep constructor light; hooks are registered in init()
    }

    /**
     * Initialize hooks (call from Plugin class at proper time)
     */
    public function init() {
        // generate shortcode when sp_product saved
        add_action( 'save_post_' . $this->post_type, [ $this, 'generate_and_save_shortcode' ], 10, 3 );

        // show admin meta box
        add_action( 'add_meta_boxes', [ $this, 'add_shortcode_metabox' ] );

        // regenerate handler (admin-post)
        add_action( 'admin_post_' . self::REGEN_ACTION, [ $this, 'handle_regenerate_action' ] );

        // register public shortcode
        add_shortcode( 'spt_table', [ $this, 'shortcode_handler' ] );

        // Prevent redirect to post.php after saving sp_product.
      add_filter( 'redirect_post_location', [ $this, 'maybe_redirect_sp_product_after_save' ], 9999, 2 );

    }

    /**
     * Generate and save shortcode on save_post_sp_product
     *
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     * @return void
     */
    public function generate_and_save_shortcode( $post_id, $post, $update ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // If shortcode already exists, keep it (change this if you want auto-regenerate)
        $existing = get_post_meta( $post_id, self::META_KEY_SHORTCODE, true );
        if ( ! empty( $existing ) ) {
            return;
        }

        $token = $this->create_token( $post_id );
        $shortcode = sprintf( '[spt_table id="%d" token="%s"]', $post_id, $token );

        update_post_meta( $post_id, self::META_KEY_SHORTCODE, $shortcode );
        update_post_meta( $post_id, self::META_KEY_TOKEN, $token );
    }

    /**
     * Create reasonably unique token
     *
     * @param int $post_id
     * @return string
     */
    protected function create_token( $post_id ) {
        if ( function_exists( 'wp_generate_uuid4' ) ) {
            return wp_generate_uuid4();
        }
        $raw = $post_id . '|' . microtime( true ) . '|' . wp_rand();
        return substr( wp_hash( $raw ), 0, 12 );
    }

    /**
     * Add meta box in the sidebar for post edit screen
     */
    public function add_shortcode_metabox() {
        add_meta_box(
            'spt_shortcode_box',
            __( 'Product Shortcode', 'smart-product-table' ),
            [ $this, 'render_shortcode_metabox' ],
            $this->post_type,
            'side',
            'high'
        );
    }

    /**
     * Render the meta box
     *
     * @param WP_Post $post
     */
    public function render_shortcode_metabox( $post ) {
        $post_id = $post->ID;
        $shortcode = get_post_meta( $post_id, self::META_KEY_SHORTCODE, true );

        if ( empty( $shortcode ) ) {
            echo '<p>' . esc_html__( 'Shortcode will be generated when you save/publish this product.', 'smart-product-table' ) . '</p>';
            return;
        }
        ?>
        <p><strong><?php esc_html_e( 'Use this shortcode', 'smart-product-table' ); ?></strong></p>
        <p>
            <input type="text" readonly onfocus="this.select()" value="<?php echo esc_attr( $shortcode ); ?>" style="width:100%; box-sizing: border-box;">
        </p>
        <p>
            <button type="button" class="button" onclick="(function(){ navigator.clipboard && navigator.clipboard.writeText(<?php echo wp_json_encode( $shortcode ); ?>); })();"><?php esc_html_e( 'Copy', 'smart-product-table' ); ?></button>
        </p>

        <hr>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'spt_regenerate_nonce', 'spt_regenerate_nonce_field' ); ?>
            <input type="hidden" name="action" value="<?php echo esc_attr( self::REGEN_ACTION ); ?>">
            <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
            <p>
                <input type="submit" class="button button-secondary" value="<?php echo esc_attr__( 'Regenerate Shortcode', 'smart-product-table' ); ?>"
                    onclick="return confirm('<?php echo esc_js( __( 'Are you sure? This will replace the existing shortcode/token for this product.', 'smart-product-table' ) ); ?>');">
            </p>
        </form>
        <?php
    }

    /**
     * Handle regenerate action from admin-post
     */
    public function handle_regenerate_action() {
        if ( ! isset( $_POST['spt_regenerate_nonce_field'] ) || ! wp_verify_nonce( wp_unslash( $_POST['spt_regenerate_nonce_field'] ), 'spt_regenerate_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'smart-product-table' ), esc_html__( 'Error', 'smart-product-table' ), [ 'response' => 403 ] );
        }

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        if ( $post_id <= 0 || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_die( esc_html__( 'Invalid post ID or insufficient permissions.', 'smart-product-table' ), esc_html__( 'Error', 'smart-product-table' ), [ 'response' => 403 ] );
        }

        $token = $this->create_token( $post_id );
        $shortcode = sprintf( '[spt_table id="%d" token="%s"]', $post_id, $token );

        update_post_meta( $post_id, self::META_KEY_TOKEN, $token );
        update_post_meta( $post_id, self::META_KEY_SHORTCODE, $shortcode );

        // Redirect back to edit screen (or referer)
        $ref = wp_get_referer();
        if ( $ref ) {
            wp_safe_redirect( $ref );
        } else {
            $redirect = add_query_arg( [ 'post' => $post_id, 'action' => 'edit', 'spt_regenerated' => '1' ], admin_url( 'post.php' ) );
            wp_safe_redirect( $redirect );
        }
        exit;
    }

    /**
     * Shortcode handler: [spt_table id="123" token="..."]
     *
     * Replace or extend rendering logic as required.
     */
    public function shortcode_handler( $atts ) {
        $atts = shortcode_atts( [
            'id'    => 0,
            'token' => '',
        ], $atts, 'spt_table' );

        $post_id = intval( $atts['id'] );
        if ( $post_id <= 0 ) {
            return '';
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== $this->post_type ) {
            return '';
        }

        $saved_token = get_post_meta( $post_id, self::META_KEY_TOKEN, true );
        if ( ! empty( $atts['token'] ) && $atts['token'] !== $saved_token ) {
            return ''; // token mismatch -> no output
        }

        // simple render (replace with TemplateRenderer if you have it)
        $title = get_the_title( $post_id );
        $description = get_post_meta( $post_id, '_smarttable_description', true );

        ob_start();
        ?>
        <div class="spt-shortcode-output spt-product-<?php echo esc_attr( $post_id ); ?>">
            <h3><?php echo esc_html( $title ); ?></h3>
            <?php if ( $description ) : ?>
                <p><?php echo esc_html( $description ); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Filter callback: after saving sp_product, try to redirect back to referer or post list
     *
     * @param string $location
     * @param int    $post_id
     * @return string
     */
// replace method with:
public function maybe_redirect_sp_product_after_save( $location, $post_id ) {
    //error_log( "[SHT] maybe_redirect_sp_product_after_save fired for post {$post_id}, orig_loc={$location}" );
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== $this->post_type ) {
        return $location;
    }

    $ref = wp_get_referer();
    if ( $ref && strpos( $ref, admin_url() ) === 0 ) {
        // don't redirect back to post.php (edit screen) — if referer contains post.php, send to list instead
        if ( strpos( $ref, 'post.php' ) !== false ) {
            return admin_url( 'edit.php?post_type=' . $this->post_type );
        }
        return esc_url_raw( $ref );
    }

    // fallback: go to CPT list
    return admin_url( 'edit.php?post_type=' . $this->post_type );
}

}

// Note: do NOT auto-init here. Initialize from your Plugin class when appropriate:
// \SmartTable\Core\ShortcodeManager::get_instance()->init();

