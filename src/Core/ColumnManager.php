<?php
namespace SmartTable\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Central registry for table columns.
 *
 * Responsibilities:
 * - Register core columns (title, price, sku, image, add_to_cart)
 * - Allow third-parties to add/remove columns via filters
 * - Resolve the active, ordered list of columns requested by the UI/shortcode
 */
class ColumnManager {

    /**
     * @var array<string, object> Map of column_id => column_instance
     */
    protected $columns = [];

    /**
     * @var bool Whether default columns and filters have already been applied.
     */
    protected bool $booted = false;

    /**
     * Singleton instance.
     *
     * @return self
     */
    public static function instance() : self {
        static $inst = null;
        if ( null === $inst ) {
            $inst = new self();
        }
        return $inst;
    }

    /**
     * Boot the manager: register defaults and run filters.
     *
     * Call this early (e.g., during Plugin::initialize()).
     */
    public function boot() : void {
        if ( $this->booted ) {
            return;
        }

        $this->register_default_columns();

        /**
         * Filter: allow external registration or modification of columns.
         *
         * Usage:
         * add_filter( 'smarttable_register_columns', function( $columns, $manager ) {
         *     $columns['custom'] = new \Vendor\Plugin\Columns\CustomColumn();
         *     return $columns;
         * }, 10, 2 );
         */
        $this->columns = apply_filters( 'smarttable_register_columns', $this->columns, $this );

        $this->booted = true;
    }

    /**
     * Register a column instance.
     *
     * The object should provide at least:
     * - get_id(): string
     * - get_label(): string
     * - render_cell( $product ): string
     *
     * @param object $column
     * @return $this
     */
    public function register( $column ) : self {
        $id = $this->normalize_id( $column );
        if ( $id ) {
            $this->columns[ $id ] = $column;
        }
        return $this;
    }

    /**
     * Remove a column by ID.
     *
     * @param string $id
     * @return $this
     */
    public function remove( string $id ) : self {
        $id = sanitize_key( $id );
        unset( $this->columns[ $id ] );
        return $this;
    }

    /**
     * Get a column instance by ID, or null.
     *
     * @param string $id
     * @return object|null
     */
    public function get( string $id ) {
        $id = sanitize_key( $id );
        return $this->columns[ $id ] ?? null;
    }

    /**
     * Get all registered columns (unordered map).
     *
     * @return array<string, object>
     */
    public function all() : array {
        return $this->columns;
    }

    /**
     * Resolve active columns in the requested order.
     *
     * @param string|array $requested  e.g., "image,title,price,add_to_cart" or ['image','title']
     * @return array<string, object>   ordered map of id => column
     */
    public function resolve_active( $requested ) : array {
        if ( is_string( $requested ) ) {
            $requested = array_map( 'trim', explode( ',', $requested ) );
        }

        $requested = array_filter( array_map( 'sanitize_key', (array) $requested ) );

        // If none requested, fall back to all default IDs, preserving their registration order.
        if ( empty( $requested ) ) {
            $requested = array_keys( $this->columns );
        }

        $active = [];
        foreach ( $requested as $id ) {
            if ( isset( $this->columns[ $id ] ) ) {
                $active[ $id ] = $this->columns[ $id ];
            }
        }

        /**
         * Filter: allow last‑minute changes to the active columns.
         *
         * @param array<string, object> $active
         * @param array<string>         $requested
         * @param ColumnManager         $this
         */
        $active = apply_filters( 'smarttable_active_columns', $active, $requested, $this );

        return $active;
    }

    /**
     * Convenience: Get display labels for a set of columns.
     *
     * @param array<string, object> $columns
     * @return array<string, string> id => label
     */
    public function get_labels( array $columns ) : array {
        $labels = [];
        foreach ( $columns as $id => $col ) {
            $label = method_exists( $col, 'get_label' )
                ? (string) $col->get_label()
                : ucwords( str_replace( '_', ' ', $id ) );
            $labels[ $id ] = $label;
        }
        return $labels;
    }

    /**
     * Normalize/derive a column ID from an object that has get_id().
     *
     * @param mixed $column
     * @return string
     */
    protected function normalize_id( $column ) : string {
        if ( is_object( $column ) && method_exists( $column, 'get_id' ) ) {
            return sanitize_key( (string) $column->get_id() );
        }
        return '';
    }

    /**
     * Register built-in columns, if their classes are available.
     * (We skip non-existing classes to avoid fatals during early scaffolding.)
     */
    protected function register_default_columns() : void {
        $map = [
            'title'             => '\\SmartTable\\Columns\\TitleColumn',
            'price'             => '\\SmartTable\\Columns\\PriceColumn',
            'sku'               => '\\SmartTable\\Columns\\SKUColumn',
            'image'             => '\\SmartTable\\Columns\\ImageColumn',
            'category'          => '\\SmartTable\\Columns\\CategoryColumn',
            'stock_status'      => '\\SmartTable\\Columns\\StockStatusColumn',
            'short_description' => '\\SmartTable\\Columns\\ShortDescriptionColumn',
            'tags'              => '\\SmartTable\\Columns\\TagsColumn',
            'rating'            => '\\SmartTable\\Columns\\RatingColumn',
            'custom_field'      => '\\SmartTable\\Columns\\CustomFieldColumn',
            'add_to_cart'       => '\\SmartTable\\Columns\\AddToCartColumn',
        ];

        foreach ( $map as $id => $class ) {
            if ( class_exists( $class ) ) {
                $instance = new $class(); // assumes column class implements get_id()
                $this->columns[ $id ] = $instance;
            }
        }
    }
}
