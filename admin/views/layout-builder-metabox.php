<?php wp_nonce_field('smarttable_layout_builder', 'smarttable_column_layout_nonce'); ?>

<div class="postbox smarttable-layout-builder-wrap">
  <div class="inside">
    <div id="smarttable-layout-builder">
      <div class="smarttable-columns-pool">
        <h2 class="hndle ui-sortable-handle">Available Columns</h2>
        <ul id="available-columns" class="columns-list">
          <li data-type="title"><span class="drag-handle">☰</span> <span class="column-icon">📝</span> Product Title</li>
          <li data-type="price"><span class="drag-handle">☰</span> <span class="column-icon">💲</span> Price</li>
          <li data-type="image"><span class="drag-handle">☰</span> <span class="column-icon">🖼️</span> Image</li>
          <li data-type="add_to_cart"><span class="drag-handle">☰</span> <span class="column-icon">🛒</span> Add to Cart</li>
          <li data-type="sku"><span class="drag-handle">☰</span> <span class="column-icon">🔢</span> SKU</li>
          <li data-type="stock_status"><span class="drag-handle">☰</span> <span class="column-icon">📦</span> Stock Status</li>
          <li data-type="short_description"><span class="drag-handle">☰</span> <span class="column-icon">🗒️</span> Short Description</li>
          <li data-type="category"><span class="drag-handle">☰</span> <span class="column-icon">📁</span> Categories</li>
          <li data-type="tags"><span class="drag-handle">☰</span> <span class="column-icon">🏷️</span> Tags</li>
          <li data-type="rating"><span class="drag-handle">☰</span> <span class="column-icon">⭐</span> Rating</li>
          <li data-type="custom_field"><span class="drag-handle">☰</span> <span class="column-icon">⚙️</span> Custom Field</li>
        </ul>
      </div>
      <div class="smarttable-columns-active">
        <div class="smarttable-presets">
          <label for="preset-layout-selector"><strong>Load a Preset Layout:</strong></label><br>
          <select id="preset-layout-selector" class="widefat">
            <option value="">— Select a preset —</option>
            <option value="basic">Basic Table</option>
            <option value="comparison">Comparison Table</option>
            <option value="catalog">Catalog View</option>
          </select>
          <button id="load-preset-layout" type="button" class="button" style="margin-top: 5px;">Load Preset</button>
          <div class="smarttable-save-preset" style="margin-top: 10px;">
            <input type="text" id="new-preset-name" placeholder="Name this preset..." class="regular-text" />
            <button id="save-custom-preset" type="button" class="button">Save as Preset</button>
          </div>
        </div>
        <h2 class="hndle ui-sortable-handle">Active Layout</h2>
        <ul id="active-columns" class="columns-list">
          <!--
            Example structure for an active column (populated dynamically by JS):
            <li data-type="title">
              <span class="drag-handle">☰</span>
              <span class="column-icon">📝</span> Product Title
              <span class="remove-column dashicons dashicons-no-alt" title="Remove column"></span>
            </li>
          -->
        </ul>
      </div>

      <!-- Settings Panel Template -->
      <div id="smarttable-column-settings-panel" style="display:none;">
        <div class="settings-panel-inner">
          <h2 class="hndle ui-sortable-handle">Column Settings</h2>
          <div class="settings-fields">
            <label>
              Label<br>
              <input type="text" id="column-setting-label" class="widefat" />
            </label>

            <div id="option-price" class="column-option" style="display:none;">
              <label><input type="checkbox" id="price-show-regular" /> Show regular price</label><br>
              <label><input type="checkbox" id="price-show-sale" /> Show sale price</label>
            </div>

            <div id="option-image" class="column-option" style="display:none;">
              <label>Image Size<br>
                <select id="image-size" class="widefat">
                  <option value="thumbnail">Thumbnail</option>
                  <option value="medium">Medium</option>
                  <option value="large">Large</option>
                  <option value="full">Full</option>
                </select>
              </label>
            </div>

            <div id="option-custom_field" class="column-option" style="display:none;">
              <label>
                Meta Key<br>
                <input type="text" id="column-setting-meta" class="widefat" />
              </label>
              <label>
                Fallback Value<br>
                <input type="text" id="column-setting-meta-fallback" class="widefat" />
              </label>
            </div>

            <div id="option-add_to_cart" class="column-option" style="display:none;">
              <label><input type="checkbox" id="addtocart-show-qty" /> Show quantity input</label>
            </div>

            <div id="option-title" class="column-option" style="display:none;">
              <label><input type="checkbox" id="title-clickable" /> Make title clickable</label>
            </div>

            <div id="option-category" class="column-option" style="display:none;">
              <label><input type="checkbox" id="category-hierarchical" /> Show hierarchical view</label>
            </div>

            <div id="option-tags" class="column-option" style="display:none;">
              <label><input type="checkbox" id="tags-link" /> Make tags clickable</label>
            </div>

            <div id="option-rating" class="column-option" style="display:none;">
              <label><input type="checkbox" id="rating-show-count" /> Show number of ratings</label>
            </div>

            <div id="option-sku" class="column-option" style="display:none;">
              <label><input type="checkbox" id="sku-hide-if-empty" /> Hide if empty</label>
            </div>

            <div id="option-stock_status" class="column-option" style="display:none;">
              <label><input type="checkbox" id="stock-highlight-low" /> Highlight if low stock</label>
            </div>
          </div>
          <button type="button" class="button button-primary" id="save-column-settings">Save</button>
          <button type="button" class="button" id="cancel-column-settings">Cancel</button>
        </div>
      </div>
      <input type="hidden" name="smarttable_column_layout" id="smarttable_column_layout" value='<?php echo esc_attr(json_encode($layout_data)); ?>'>
    </div>
  </div>
</div>

<div id="smarttable-live-preview" class="smarttable-live-preview postbox">
  <h2 class="hndle ui-sortable-handle">Live Preview</h2>
  <div class="inside preview-content">
    <p class="description">Use the layout builder to see how your table will appear.</p>
    <div class="smarttable-preview-table">
      <p class="no-preview-placeholder">Start building your table layout to preview it here.</p>
    </div>
  </div>
</div>