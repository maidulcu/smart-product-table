<?php if ( isset( $post ) ) : ?>
	<div class="smarttable-display-options">
		
		<div class="display-header">
			<h3><i class="dashicons dashicons-admin-settings"></i> <?php esc_html_e( 'Display Settings', 'smart-product-table' ); ?></h3>
			<p><?php esc_html_e( 'Configure how your product table is displayed.', 'smart-product-table' ); ?></p>
		</div>

		<div class="display-settings">
			
			<!-- Pagination Settings -->
			<div class="setting-box">
				<div class="setting-title">
					<i class="dashicons dashicons-admin-page"></i>
					<span><?php esc_html_e( 'Pagination Settings', 'smart-product-table' ); ?></span>
				</div>
				<div class="setting-content">
					<div class="setting-row">
						<label class="setting-label">
							<input type="checkbox" name="smarttable_enable_pagination" value="1" 
								   <?php checked( get_post_meta( $post->ID, '_smarttable_enable_pagination', true ), '1' ); ?>>
							<?php esc_html_e( 'Enable Pagination', 'smart-product-table' ); ?>
						</label>
						<p class="setting-description"><?php esc_html_e( 'Show pagination controls below the table.', 'smart-product-table' ); ?></p>
					</div>
					<div class="setting-row">
						<label class="setting-label"><?php esc_html_e( 'Products Per Page', 'smart-product-table' ); ?></label>
						<input type="number" name="smarttable_per_page" 
							   value="<?php echo esc_attr( get_post_meta( $post->ID, '_smarttable_per_page', true ) ?: '12' ); ?>" 
							   min="1" max="100" class="setting-input">
						<p class="setting-description"><?php esc_html_e( 'Number of products to show per page.', 'smart-product-table' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Table Display -->
			<div class="setting-box">
				<div class="setting-title">
					<i class="dashicons dashicons-table-row-before"></i>
					<span><?php esc_html_e( 'Table Display', 'smart-product-table' ); ?></span>
				</div>
				<div class="setting-content">
					<div class="setting-row">
						<label class="setting-label">
							<input type="checkbox" name="smarttable_show_search" value="1" 
								   <?php checked( get_post_meta( $post->ID, '_smarttable_show_search', true ), '1' ); ?>>
							<?php esc_html_e( 'Show Search Box', 'smart-product-table' ); ?>
						</label>
						<p class="setting-description"><?php esc_html_e( 'Display search functionality above the table.', 'smart-product-table' ); ?></p>
					</div>
					<div class="setting-row">
						<label class="setting-label">
							<input type="checkbox" name="smarttable_show_filters" value="1" 
								   <?php checked( get_post_meta( $post->ID, '_smarttable_show_filters', true ), '1' ); ?>>
							<?php esc_html_e( 'Show Filter Options', 'smart-product-table' ); ?>
						</label>
						<p class="setting-description"><?php esc_html_e( 'Display filter controls above the table.', 'smart-product-table' ); ?></p>
					</div>
					<div class="setting-row">
						<label class="setting-label">
							<input type="checkbox" name="smarttable_show_bulk_cart" value="1" 
								   <?php checked( get_post_meta( $post->ID, '_smarttable_show_bulk_cart', true ), '1' ); ?>>
							<?php esc_html_e( 'Show Bulk Add to Cart', 'smart-product-table' ); ?>
						</label>
						<p class="setting-description"><?php esc_html_e( 'Enable bulk selection and add multiple products to cart.', 'smart-product-table' ); ?></p>
					</div>
				</div>
			</div>

		</div>

	</div>

	<style>
	.smarttable-display-options {
		background: #fff;
		border-radius: 8px;
		padding: 20px;
	}
	
	.display-header {
		margin-bottom: 25px;
		border-bottom: 2px solid #f0f0f1;
		padding-bottom: 15px;
	}
	
	.display-header h3 {
		margin: 0 0 8px 0;
		color: #1d2327;
		font-size: 18px;
	}
	
	.display-header p {
		margin: 0;
		color: #646970;
		font-size: 14px;
	}
	
	.display-settings {
		display: grid;
		gap: 20px;
	}
	
	.setting-box {
		border: 1px solid #ddd;
		border-radius: 6px;
		overflow: hidden;
	}
	
	.setting-title {
		background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
		color: white;
		padding: 12px 16px;
		font-weight: 600;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	
	.setting-content {
		padding: 16px;
	}
	
	.setting-row {
		margin-bottom: 16px;
	}
	
	.setting-row:last-child {
		margin-bottom: 0;
	}
	
	.setting-label {
		display: block;
		margin-bottom: 6px;
		font-weight: 600;
		color: #1d2327;
		cursor: pointer;
	}
	
	.setting-label input[type="checkbox"] {
		margin-right: 8px;
	}
	
	.setting-input {
		width: 100px;
		padding: 6px 10px;
		border: 1px solid #ddd;
		border-radius: 4px;
	}
	
	.setting-description {
		margin: 4px 0 0 0;
		color: #646970;
		font-size: 13px;
		font-style: italic;
	}
	</style>

<?php endif; ?>