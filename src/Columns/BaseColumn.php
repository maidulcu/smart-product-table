<?php

namespace SmartTable\Columns;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

abstract class BaseColumn implements \SmartTable\Interfaces\ColumnInterface {

    protected $id;
    protected $label;

    public function __construct( $id, $label ) {
        $this->id = $id;
        $this->label = $label;
    }

    public function get_id(): string {
        return $this->id;
    }

    public function get_label(): string {
        return $this->label;
    }

    abstract public function render_cell( $product, array $context = [] ): string;

    protected function esc_html( $string ) {
        return htmlspecialchars( $string, ENT_QUOTES, 'UTF-8' );
    }
}
