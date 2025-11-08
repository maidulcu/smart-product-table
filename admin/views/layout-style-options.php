<?php if ( isset( $post ) ) : ?>
	<div class="smarttable-style-options">
		<fieldset style="margin-bottom: 20px;">
			<legend><strong><?php esc_html_e( 'Product Table Styles', 'smart-product-table' ); ?></strong></legend>
			
			<?php
			$selected_style = get_post_meta( $post->ID, '_smarttable_design_style', true ) ?: 'default';
			$styles = [
				'default' => [
					'name' => __( 'Standard Grid', 'smart-product-table' ),
					'preview' => 'Clean product grid with hover effects'
				],
				'compact' => [
					'name' => __( 'Compact List', 'smart-product-table' ),
					'preview' => 'Dense product listing for more items'
				],
				'card' => [
					'name' => __( 'Product Cards', 'smart-product-table' ),
					'preview' => 'Card-style layout with product images'
				],
				'striped' => [
					'name' => __( 'Striped Rows', 'smart-product-table' ),
					'preview' => 'Alternating row colors for easy reading'
				],
				'modern' => [
					'name' => __( 'Modern Shop', 'smart-product-table' ),
					'preview' => 'Contemporary e-commerce design'
				]
			];
			?>
			
			<div class="smarttable-style-grid">
				<?php foreach ( $styles as $style_key => $style_data ) : ?>
					<div class="style-option <?php echo $selected_style === $style_key ? 'selected' : ''; ?>">
						<label>
							<input type="radio" name="smarttable_design_style" value="<?php echo esc_attr( $style_key ); ?>" <?php checked( $selected_style, $style_key ); ?> />
							<div class="style-preview style-preview-<?php echo esc_attr( $style_key ); ?>">
								<table class="preview-table smarttable-style-<?php echo esc_attr( $style_key ); ?>">
									<thead>
										<tr class="preview-header">
											<th class="product-image">Image</th>
											<th class="product-title">Product</th>
											<th class="product-price">Price</th>
											<th class="add-to-cart">Cart</th>
										</tr>
									</thead>
									<tbody>
										<tr class="preview-row">
											<td class="product-image"><img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMwIiBoZWlnaHQ9IjMwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMCAxMEgyMFYyMEgxMFYxMFoiIGZpbGw9IiNEMUQ1REIiLz4KPC9zdmc+" alt="Product" /></td>
											<td class="product-title"><a href="#">iPhone 15 Pro</a></td>
											<td class="product-price">$999.00</td>
											<td class="add-to-cart"><button class="button">Add to Cart</button></td>
										</tr>
										<tr class="preview-row">
											<td class="product-image"><img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMwIiBoZWlnaHQ9IjMwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMCAxMEgyMFYyMEgxMFYxMFoiIGZpbGw9IiNEMUQ1REIiLz4KPC9zdmc+" alt="Product" /></td>
											<td class="product-title"><a href="#">MacBook Pro</a></td>
											<td class="product-price">$1999.00</td>
											<td class="add-to-cart"><button class="button">Add to Cart</button></td>
										</tr>
									</tbody>
								</table>
							</div>
							<h4><?php echo esc_html( $style_data['name'] ); ?></h4>
							<p><?php echo esc_html( $style_data['preview'] ); ?></p>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
		</fieldset>
	</div>
<?php endif; ?>