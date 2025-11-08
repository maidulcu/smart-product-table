<?php
namespace SmartTable\Ajax;

use SmartTable\Core\ColumnManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles AJAX requests for Smart Product Table
 *
 * JS expects:
 *  - action: smarttable_load_table
 *  - page: int
 *  - sort_by: string (optional)
 *  - sort_order: ASC|DESC (optional)
 *  - filters: serialized array from a form (optional)
 *  - columns: comma list (optional) – fallback to defaults
 *  - limit: int (optional) – fallback to 10
 */
class TableAjaxHandler {

    public function __construct() {
        add_action( 'wp_ajax_smarttable_load_table', [ $this, 'load_table' ] );
        add_action( 'wp_ajax_nopriv_smarttable_load_table', [ $this, 'load_table' ] );
    }

    /**
     * Main AJAX endpoint: returns table <tbody> HTML and pagination HTML.
     */
  /**
 * Main AJAX endpoint: returns table <tbody> HTML and pagination HTML.
 */
public function load_table() : void {
    // Security: Nonce verification (required by WPCS). Verify BEFORE reading $_POST.
    $nonce = isset( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'smarttable_load_table' ) ) {
        wp_send_json_error(
            [ 'message' => __( 'Invalid request. Please refresh and try again.', 'smart-product-table' ) ],
            403
        );
    }

    // Input sanitization.
    $page       = isset( $_POST['page'] ) ? max( 1, (int) $_POST['page'] ) : 1;
    $sort_by    = isset( $_POST['sort_by'] ) ? sanitize_key( wp_unslash( $_POST['sort_by'] ) ) : 'date';
    $sort_order = isset( $_POST['sort_order'] ) && strtoupper( sanitize_text_field( wp_unslash( $_POST['sort_order'] ) ) ) === 'ASC' ? 'ASC' : 'DESC';
    $limit      = isset( $_POST['limit'] ) ? max( 1, (int) $_POST['limit'] ) : 10;

    // Columns: accept comma list or array.
    $columns = [];
    if ( isset( $_POST['columns'] ) ) {
       $columns = $this->normalize_list(
    sanitize_text_field( wp_unslash( $_POST['columns'] ) )
);
    }
    if ( empty( $columns ) ) {
        $columns = [ 'image', 'title', 'price', 'sku', 'add_to_cart' ];
    }


   // Filters: accept serialized array or plain POST vars (category, tag).
// Filters: accept serialized array or plain POST vars (category, tag).
$category = [];
$tag      = [];

// Ensure 'filters' exists and is expected shape. First check existence.
if ( isset( $_POST['filters'] ) ) {
    // Uns lash the incoming value first (WP adds slashes to globals).
    $raw_filters = wp_unslash( $_POST['filters'] );

    // If JS sent JSON string, try decode safely:
    if ( is_string( $raw_filters ) ) {
        $decoded = json_decode( $raw_filters, true );
        if ( is_array( $decoded ) ) {
            $raw_filters = $decoded;
        } else {
            // Not an array after decode — normalize to empty array to avoid warnings.
            $raw_filters = [];
        }
    }

    // Now $raw_filters should be an array (or cast to one).
    if ( is_array( $raw_filters ) ) {
        foreach ( $raw_filters as $pair ) {
            if ( ! is_array( $pair ) || ! isset( $pair['name'], $pair['value'] ) ) {
                continue;
            }

            // Elements are already unslashed; sanitize each element properly.
            $name  = sanitize_key( (string) $pair['name'] );
            $value = sanitize_text_field( (string) $pair['value'] );

            if ( $name === 'category' && $value !== '' ) {
                $category = array_merge( $category, $this->normalize_list( $value ) );
            }
            if ( $name === 'tag' && $value !== '' ) {
                $tag = array_merge( $tag, $this->normalize_list( $value ) );
            }
        }
    }
} else {
    // Backwards-compatible: accept direct category/tag POST fields
    if ( isset( $_POST['category'] ) ) {
        $category = $this->normalize_list( wp_unslash( $_POST['category'] ) );
    }
    if ( isset( $_POST['tag'] ) ) {
        $tag = $this->normalize_list( wp_unslash( $_POST['tag'] ) );
    }
}



    // Ensure WooCommerce is available.
    if ( ! function_exists( 'wc_get_products' ) ) {
        wp_send_json_error( [ 'message' => __( 'WooCommerce is required.', 'smart-product-table' ) ] );
    }

    // Boot the column manager and resolve the active column objects.
    if ( class_exists( '\\SmartTable\\Core\\ColumnManager' ) ) {
        ColumnManager::instance()->boot();
    }
    $manager     = ColumnManager::instance();
    $active_cols = $manager->resolve_active( $columns );

    // Query products with pagination. WooCommerce supports paginate => true.
    $args = [
        'status'   => 'publish',
        'limit'    => $limit,
        'page'     => $page,
        'orderby'  => $sort_by,
        'order'    => $sort_order,
        'paginate' => true,
    ];
    if ( ! empty( $category ) ) {
        $args['category'] = $category;
    }
    if ( ! empty( $tag ) ) {
        $args['tag'] = $tag;
    }

    $result = wc_get_products( $args );
    // $result is array: ['products' => \WC_Product[], 'total' => int, 'pages' => int]
    $products = isset( $result->products ) ? $result->products : ( $result['products'] ?? [] );
    $total    = isset( $result->total ) ? (int) $result->total : (int) ( $result['total'] ?? 0 );
    $pages    = isset( $result->max_num_pages ) ? (int) $result->max_num_pages : (int) ( $result['pages'] ?? 1 );

    // Render tbody HTML.
    $tbody_html = $this->render_tbody( $products, $active_cols, [
        'columns'  => $columns,
        'limit'    => $limit,
        'category' => $category,
        'tag'      => $tag,
        'orderby'  => $sort_by,
        'order'    => $sort_order,
    ] );

    // Render basic pagination HTML.
    $pagination_html = $this->render_pagination( $page, max( 1, $pages ) );

    wp_send_json_success( [
        'table_body' => $tbody_html,
        'pagination' => $pagination_html,
        'total'      => $total,
        'pages'      => $pages,
        'page'       => $page,
    ] );
}


    /**
     * Render only the <tbody> based on product list and active columns.
     *
     * @param array $products
     * @param array $active_cols map of id => column object
     * @param array $context
     * @return string
     */
    protected function render_tbody( array $products, array $active_cols, array $context ) : string {
        ob_start();

        if ( ! empty( $products ) ) :
            foreach ( $products as $product ) : ?>
                <tr class="smarttable-row" data-product-id="<?php echo esc_attr( (string) $product->get_id() ); ?>">
                    <?php foreach ( $active_cols as $id => $col ) : ?>
                        <td class="smarttable-cell smarttable-col-<?php echo esc_attr( $id ); ?>">
                            <?php
                            $html = '';
                            if ( is_object( $col ) && method_exists( $col, 'render_cell' ) ) {
                                $html = (string) $col->render_cell( $product, $context );
                            }
                            echo wp_kses_post( $html );
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php
            endforeach;
        else : ?>
            <tr>
                <td colspan="<?php echo esc_attr( (string) max( 1, count( $active_cols ) ) ); ?>">
                    <?php esc_html_e( 'No products found.', 'smart-product-table' ); ?>
                </td>
            </tr>
        <?php
        endif;

        return (string) ob_get_clean();
    }

    /**
     * Very simple pagination HTML renderer (previous/next + numbered links).
     *
     * @param int $current
     * @param int $total_pages
     * @return string
     */
    protected function render_pagination( int $current, int $total_pages ) : string {
        if ( $total_pages <= 1 ) {
            return '';
        }

        ob_start(); ?>
        <nav class="smarttable-pagination" aria-label="<?php esc_attr_e( 'Product table pagination', 'smart-product-table' ); ?>">
            <ul class="smarttable-pages">
                <?php
                // Prev
                if ( $current > 1 ) :
                    $prev = $current - 1; ?>
                    <li class="page-item prev"><a href="#" data-page="<?php echo esc_attr( (string) $prev ); ?>">&laquo;</a></li>
                <?php endif; ?>

                <?php
                // Windowed page list
                $window = 2;
                $start  = max( 1, $current - $window );
                $end    = min( $total_pages, $current + $window );

                if ( $start > 1 ) {
                    echo '<li class="page-item"><a href="#" data-page="1">1</a></li>';
                    if ( $start > 2 ) {
                        echo '<li class="page-item dots"><span>&hellip;</span></li>';
                    }
                }

             
            for ( $i = $start; $i <= $end; $i++ ) {
                $li_class = ( $i === $current ) ? 'page-item active' : 'page-item';
                $aria_val = ( $i === $current ) ? 'page' : '';

                if ( $aria_val ) {
                    printf(
                        '<li class="%s"><a href="#" data-page="%s" aria-current="%s">%s</a></li>',
                        esc_attr( $li_class ),
                        esc_attr( (string) $i ),
                        esc_attr( $aria_val ),
                        esc_html( (string) $i )
                    );
                } else {
                    printf(
                        '<li class="%s"><a href="#" data-page="%s">%s</a></li>',
                        esc_attr( $li_class ),
                        esc_attr( (string) $i ),
                        esc_html( (string) $i )
                    );
                }
            }


                if ( $end < $total_pages ) {
                    if ( $end < $total_pages - 1 ) {
                        echo '<li class="page-item dots"><span>&hellip;</span></li>';
                    }
                    echo '<li class="page-item"><a href="#" data-page="' . esc_attr( (string) $total_pages ) . '">' . esc_html( (string) $total_pages ) . '</a></li>';
                }

                // Next
                if ( $current < $total_pages ) :
                    $next = $current + 1; ?>
                    <li class="page-item next"><a href="#" data-page="<?php echo esc_attr( (string) $next ); ?>">&raquo;</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * Accepts comma-separated string or array; returns array of sanitized slugs.
     *
     * @param string|array $value
     * @return array
     */
    protected function normalize_list( $value ) : array {
        if ( is_array( $value ) ) {
            return array_values( array_filter( array_map( 'sanitize_title', $value ) ) );
        }
        $value = (string) $value;
        if ( $value === '' ) {
            return [];
        }
        $parts = array_map( 'trim', explode( ',', $value ) );
        return array_values( array_filter( array_map( 'sanitize_title', $parts ) ) );
    }
}