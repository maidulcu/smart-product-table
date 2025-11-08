<?php if ( isset( $post ) ) : ?>
	<div class="smarttable-advanced-filters">
		
		<!-- Filter Header -->
		<div class="filter-header">
			<h3><?php esc_html_e( 'Advanced Product Filters', 'smart-product-table' ); ?></h3>
			<p class="filter-description"><?php esc_html_e( 'Configure which products to display in your table using these advanced filtering options.', 'smart-product-table' ); ?></p>
		</div>

		<!-- Filter Tabs -->
		<div class="filter-tabs">
			<button type="button" class="filter-tab active" data-tab="taxonomy"><?php esc_html_e( 'Categories & Tags', 'smart-product-table' ); ?></button>
			<button type="button" class="filter-tab" data-tab="attributes"><?php esc_html_e( 'Product Attributes', 'smart-product-table' ); ?></button>
			<button type="button" class="filter-tab" data-tab="pricing"><?php esc_html_e( 'Pricing & Stock', 'smart-product-table' ); ?></button>
			<button type="button" class="filter-tab" data-tab="advanced"><?php esc_html_e( 'Advanced Options', 'smart-product-table' ); ?></button>
		</div>

		<!-- Tab Content -->
		<div class="filter-content">
			
			<!-- Taxonomy Filters Tab -->
			<div class="filter-panel active" id="taxonomy-panel">
				<?php
				$selected_categories = array_map( 'sanitize_title', explode( ',', get_post_meta( $post->ID, '_smarttable_filter_categories', true ) ) );
				$selected_tags = array_map( 'sanitize_title', explode( ',', get_post_meta( $post->ID, '_smarttable_filter_tags', true ) ) );
				?>
				
				<div class="filter-row">
					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Product Categories', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Select categories to include', 'smart-product-table' ); ?></span>
						</label>
						<select id="smarttable_filter_categories" name="smarttable_filter_categories[]" class="filter-select" multiple>
							<?php
							$categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
							foreach ( $categories as $cat ) {
								printf(
									'<option value="%1$s"%2$s>%3$s (%4$d)</option>',
									esc_attr( $cat->slug ),
									in_array( $cat->slug, $selected_categories, true ) ? ' selected' : '',
									esc_html( $cat->name ),
									$cat->count
								);
							}
							?>
						</select>
					</div>

					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Product Tags', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Select tags to include', 'smart-product-table' ); ?></span>
						</label>
						<select id="smarttable_filter_tags" name="smarttable_filter_tags[]" class="filter-select" multiple>
							<?php
							$tags = get_terms(['taxonomy' => 'product_tag', 'hide_empty' => false]);
							foreach ( $tags as $tag ) {
								printf(
									'<option value="%1$s"%2$s>%3$s (%4$d)</option>',
									esc_attr( $tag->slug ),
									in_array( $tag->slug, $selected_tags, true ) ? ' selected' : '',
									esc_html( $tag->name ),
									$tag->count
								);
							}
							?>
						</select>
					</div>
				</div>

				<div class="filter-row">
					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Filter Logic', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'How to match multiple filters', 'smart-product-table' ); ?></span>
						</label>
						<select id="smarttable_tax_query_relation" name="smarttable_tax_query_relation" class="filter-select">
							<?php
							$relation = get_post_meta( $post->ID, '_smarttable_tax_query_relation', true );
							$options = [
								'AND' => __( 'Match All (AND) - Product must have all selected categories/tags', 'smart-product-table' ),
								'OR'  => __( 'Match Any (OR) - Product can have any selected category/tag', 'smart-product-table' ),
							];
							foreach ( $options as $key => $label ) {
								printf(
									'<option value="%1$s"%2$s>%3$s</option>',
									esc_attr( $key ),
									selected( strtoupper( $relation ), $key, false ),
									esc_html( $label )
								);
							}
							?>
						</select>
					</div>
				</div>
			</div>

			<!-- Product Attributes Tab -->
			<div class="filter-panel" id="attributes-panel">
				<div class="filter-row">
					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Product Type', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Filter by product type', 'smart-product-table' ); ?></span>
						</label>
						<select name="smarttable_product_type[]" class="filter-select" multiple>
							<?php
							$selected_types = get_post_meta( $post->ID, '_smarttable_product_type', true );
							$selected_types = $selected_types ? explode(',', $selected_types) : [];
							$product_types = ['simple' => 'Simple', 'variable' => 'Variable', 'grouped' => 'Grouped', 'external' => 'External'];
							foreach ( $product_types as $type => $label ) {
								printf(
									'<option value="%1$s"%2$s>%3$s</option>',
									esc_attr( $type ),
									in_array( $type, $selected_types ) ? ' selected' : '',
									esc_html( $label )
								);
							}
							?>
						</select>
					</div>

					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Featured Products', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Include only featured products', 'smart-product-table' ); ?></span>
						</label>
						<select name="smarttable_featured_only" class="filter-select">
							<?php
							$featured_only = get_post_meta( $post->ID, '_smarttable_featured_only', true );
							?>
							<option value=""><?php esc_html_e( 'All Products', 'smart-product-table' ); ?></option>
							<option value="yes" <?php selected( $featured_only, 'yes' ); ?>><?php esc_html_e( 'Featured Only', 'smart-product-table' ); ?></option>
							<option value="no" <?php selected( $featured_only, 'no' ); ?>><?php esc_html_e( 'Non-Featured Only', 'smart-product-table' ); ?></option>
						</select>
					</div>
				</div>
			</div>

			<!-- Pricing & Stock Tab -->
			<div class="filter-panel" id="pricing-panel">
				<div class="filter-row">
					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Price Range', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Filter products by price range', 'smart-product-table' ); ?></span>
						</label>
						<div class="price-range-inputs">
							<input type="number" name="smarttable_min_price" placeholder="<?php esc_attr_e( 'Min Price', 'smart-product-table' ); ?>" 
								   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_min_price', true ) ); ?>" 
								   class="price-input" step="0.01" min="0">
							<span class="price-separator">—</span>
							<input type="number" name="smarttable_max_price" placeholder="<?php esc_attr_e( 'Max Price', 'smart-product-table' ); ?>" 
								   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_max_price', true ) ); ?>" 
								   class="price-input" step="0.01" min="0">
						</div>
					</div>

					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Stock Status', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Filter by stock availability', 'smart-product-table' ); ?></span>
						</label>
						<select name="smarttable_stock_status[]" class="filter-select" multiple>
							<?php
							$selected_stock = get_post_meta( $post->ID, '_smarttable_stock_status', true );
							$selected_stock = $selected_stock ? explode(',', $selected_stock) : [];
							$stock_statuses = ['instock' => 'In Stock', 'outofstock' => 'Out of Stock', 'onbackorder' => 'On Backorder'];
							foreach ( $stock_statuses as $status => $label ) {
								printf(
									'<option value="%1$s"%2$s>%3$s</option>',
									esc_attr( $status ),
									in_array( $status, $selected_stock ) ? ' selected' : '',
									esc_html( $label )
								);
							}
							?>
						</select>
					</div>
				</div>

				<div class="filter-row">
					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'On Sale Products', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Include only products on sale', 'smart-product-table' ); ?></span>
						</label>
						<select name="smarttable_on_sale" class="filter-select">
							<?php
							$on_sale = get_post_meta( $post->ID, '_smarttable_on_sale', true );
							?>
							<option value=""><?php esc_html_e( 'All Products', 'smart-product-table' ); ?></option>
							<option value="yes" <?php selected( $on_sale, 'yes' ); ?>><?php esc_html_e( 'On Sale Only', 'smart-product-table' ); ?></option>
							<option value="no" <?php selected( $on_sale, 'no' ); ?>><?php esc_html_e( 'Regular Price Only', 'smart-product-table' ); ?></option>
						</select>
					</div>
				</div>
			</div>

			<!-- Advanced Options Tab -->
			<div class="filter-panel" id="advanced-panel">
				<div class="filter-row">
					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Include Specific Products', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Enter product IDs to include (comma separated)', 'smart-product-table' ); ?></span>
						</label>
						<input type="text" id="smarttable_include_ids" name="smarttable_include_ids" class="filter-input"
							   placeholder="<?php esc_attr_e( 'e.g. 12, 34, 56', 'smart-product-table' ); ?>"
							   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_include_ids', true ) ); ?>">
					</div>

					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Exclude Specific Products', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Enter product IDs to exclude (comma separated)', 'smart-product-table' ); ?></span>
						</label>
						<input type="text" id="smarttable_exclude_ids" name="smarttable_exclude_ids" class="filter-input"
							   placeholder="<?php esc_attr_e( 'e.g. 78, 90', 'smart-product-table' ); ?>"
							   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_exclude_ids', true ) ); ?>">
					</div>
				</div>

				<div class="filter-row">
					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Date Range', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Filter products by publish date', 'smart-product-table' ); ?></span>
						</label>
						<div class="date-range-inputs">
							<input type="date" name="smarttable_date_from" 
								   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_date_from', true ) ); ?>" 
								   class="date-input">
							<span class="date-separator">to</span>
							<input type="date" name="smarttable_date_to" 
								   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_date_to', true ) ); ?>" 
								   class="date-input">
						</div>
					</div>

					<div class="filter-group">
						<label class="filter-label">
							<span class="label-text"><?php esc_html_e( 'Product Limit', 'smart-product-table' ); ?></span>
							<span class="label-help"><?php esc_html_e( 'Maximum number of products to display', 'smart-product-table' ); ?></span>
						</label>
						<input type="number" name="smarttable_product_limit" class="filter-input" min="1" max="1000"
							   placeholder="<?php esc_attr_e( 'e.g. 50', 'smart-product-table' ); ?>"
							   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_product_limit', true ) ); ?>">
					</div>
				</div>
			</div>

		</div>

		<!-- Filter Actions -->
		<div class="filter-actions">
			<button type="button" class="button button-secondary" id="reset-filters"><?php esc_html_e( 'Reset All Filters', 'smart-product-table' ); ?></button>
			<button type="button" class="button button-primary" id="preview-filters"><?php esc_html_e( 'Preview Results', 'smart-product-table' ); ?></button>
		</div>

		<!-- Filter Preview -->
		<div class="filter-preview" id="filter-preview" style="display: none;">
			<h4><?php esc_html_e( 'Filter Preview', 'smart-product-table' ); ?></h4>
			<div class="preview-content">
				<p class="preview-loading"><?php esc_html_e( 'Loading preview...', 'smart-product-table' ); ?></p>
			</div>
		</div>

	</div>
<?php endif; ?>