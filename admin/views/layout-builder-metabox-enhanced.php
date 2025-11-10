<?php wp_nonce_field('smarttable_layout_builder', 'smarttable_column_layout_nonce'); ?>

<div class="postbox smarttable-layout-builder-wrap">
  <div class="inside">
    <!-- Builder Header with Actions -->
    <div class="smarttable-builder-header">
      <div class="builder-title">
        <h3>📐 Drag & Drop Layout Builder</h3>
        <p class="description">Drag columns from available to active area, or use quick actions below</p>
      </div>
      <div class="builder-actions">
        <button type="button" id="undo-layout" class="button" title="Undo last change" disabled>
          <span class="dashicons dashicons-undo"></span> Undo
        </button>
        <button type="button" id="redo-layout" class="button" title="Redo last change" disabled>
          <span class="dashicons dashicons-redo"></span> Redo
        </button>
        <button type="button" id="clear-layout" class="button" title="Clear all columns">
          <span class="dashicons dashicons-trash"></span> Clear All
        </button>
        <button type="button" id="export-layout" class="button button-secondary" title="Export layout as JSON">
          <span class="dashicons dashicons-download"></span> Export
        </button>
        <button type="button" id="import-layout" class="button button-secondary" title="Import layout from JSON">
          <span class="dashicons dashicons-upload"></span> Import
        </button>
      </div>
    </div>

    <div id="smarttable-layout-builder">
      <div class="smarttable-columns-pool">
        <div class="pool-header">
          <h2 class="hndle ui-sortable-handle">📦 Available Columns <span class="column-count" id="available-count">(11)</span></h2>
          <div class="search-wrapper">
            <input type="text" id="column-search" class="search-input" placeholder="🔍 Search columns..." />
          </div>
        </div>
        <ul id="available-columns" class="columns-list">
          <li data-type="title" data-category="basic"><span class="drag-handle">☰</span> <span class="column-icon">📝</span> <span class="column-name">Product Title</span></li>
          <li data-type="price" data-category="basic"><span class="drag-handle">☰</span> <span class="column-icon">💲</span> <span class="column-name">Price</span></li>
          <li data-type="image" data-category="basic"><span class="drag-handle">☰</span> <span class="column-icon">🖼️</span> <span class="column-name">Image</span></li>
          <li data-type="add_to_cart" data-category="actions"><span class="drag-handle">☰</span> <span class="column-icon">🛒</span> <span class="column-name">Add to Cart</span></li>
          <li data-type="sku" data-category="details"><span class="drag-handle">☰</span> <span class="column-icon">🔢</span> <span class="column-name">SKU</span></li>
          <li data-type="stock_status" data-category="details"><span class="drag-handle">☰</span> <span class="column-icon">📦</span> <span class="column-name">Stock Status</span></li>
          <li data-type="short_description" data-category="content"><span class="drag-handle">☰</span> <span class="column-icon">🗒️</span> <span class="column-name">Short Description</span></li>
          <li data-type="category" data-category="taxonomy"><span class="drag-handle">☰</span> <span class="column-icon">📁</span> <span class="column-name">Categories</span></li>
          <li data-type="tags" data-category="taxonomy"><span class="drag-handle">☰</span> <span class="column-icon">🏷️</span> <span class="column-name">Tags</span></li>
          <li data-type="rating" data-category="social"><span class="drag-handle">☰</span> <span class="column-icon">⭐</span> <span class="column-name">Rating</span></li>
          <li data-type="custom_field" data-category="advanced"><span class="drag-handle">☰</span> <span class="column-icon">⚙️</span> <span class="column-name">Custom Field</span></li>
        </ul>
        <div class="no-results" style="display:none;">
          <p>No columns found matching your search.</p>
        </div>
      </div>

      <div class="smarttable-columns-active">
        <div class="smarttable-presets">
          <label for="preset-layout-selector"><strong>⚡ Quick Presets:</strong></label><br>
          <div class="preset-buttons">
            <button type="button" class="button preset-btn" data-preset="basic">📋 Basic</button>
            <button type="button" class="button preset-btn" data-preset="comparison">⚖️ Comparison</button>
            <button type="button" class="button preset-btn" data-preset="catalog">📚 Catalog</button>
            <button type="button" class="button preset-btn" data-preset="detailed">🔍 Detailed</button>
          </div>
          <div class="or-divider">or use selector</div>
          <select id="preset-layout-selector" class="widefat">
            <option value="">— Select a preset —</option>
            <option value="basic">Basic Table</option>
            <option value="comparison">Comparison Table</option>
            <option value="catalog">Catalog View</option>
            <option value="detailed">Detailed View</option>
          </select>
          <button id="load-preset-layout" type="button" class="button" style="margin-top: 5px;">Load Preset</button>
        </div>
        <div class="active-header">
          <h2 class="hndle ui-sortable-handle">✅ Active Layout <span class="column-count" id="active-count">(0)</span></h2>
          <div class="layout-info">
            <span class="info-badge" id="column-width-total">Total Width: 0%</span>
          </div>
        </div>
        <ul id="active-columns" class="columns-list">
          <!-- Active columns will be populated here -->
        </ul>
        <div class="empty-state" id="empty-state-message">
          <div class="empty-icon">📭</div>
          <p>No columns added yet</p>
          <small>Drag columns from the left panel or use quick presets above</small>
        </div>
      </div>

      <!-- Enhanced Settings Panel -->
      <div id="smarttable-column-settings-panel" style="display:none;">
        <div class="settings-panel-inner">
          <h2 class="hndle ui-sortable-handle">⚙️ Column Settings</h2>
          <div class="settings-fields">
            <label>
              Column Label<br>
              <input type="text" id="column-setting-label" class="widefat" placeholder="Enter custom label" />
            </label>

            <div class="settings-row">
              <label class="setting-half">
                Width (%)<br>
                <input type="number" id="column-setting-width" class="widefat" min="5" max="100" value="auto" placeholder="Auto" />
                <small>Leave empty for auto width</small>
              </label>
              <label class="setting-half">
                Alignment<br>
                <select id="column-setting-alignment" class="widefat">
                  <option value="left">⬅️ Left</option>
                  <option value="center">↔️ Center</option>
                  <option value="right">➡️ Right</option>
                </select>
              </label>
            </div>

            <label>
              <input type="checkbox" id="column-setting-sortable" /> Enable column sorting
            </label>

            <div id="option-price" class="column-option" style="display:none;">
              <label><input type="checkbox" id="price-show-regular" /> Show regular price</label><br>
              <label><input type="checkbox" id="price-show-sale" /> Show sale price</label>
            </div>

            <div id="option-image" class="column-option" style="display:none;">
              <label>Image Size<br>
                <select id="image-size" class="widefat">
                  <option value="thumbnail">Thumbnail (150x150)</option>
                  <option value="medium">Medium (300x300)</option>
                  <option value="medium_large">Medium Large (768px)</option>
                  <option value="large">Large (1024px)</option>
                  <option value="full">Full Size</option>
                </select>
              </label>
            </div>

            <div id="option-custom_field" class="column-option" style="display:none;">
              <label>
                Meta Key<br>
                <input type="text" id="column-setting-meta" class="widefat" placeholder="_custom_field_key" />
              </label>
              <label>
                Fallback Value<br>
                <input type="text" id="column-setting-meta-fallback" class="widefat" placeholder="N/A" />
              </label>
            </div>

            <div id="option-add_to_cart" class="column-option" style="display:none;">
              <label><input type="checkbox" id="addtocart-show-qty" /> Show quantity input</label>
              <label><input type="checkbox" id="addtocart-ajax" /> Enable AJAX add to cart</label>
            </div>

            <div id="option-title" class="column-option" style="display:none;">
              <label><input type="checkbox" id="title-clickable" /> Make title clickable</label>
              <label><input type="checkbox" id="title-show-excerpt" /> Show excerpt on hover</label>
            </div>

            <div id="option-category" class="column-option" style="display:none;">
              <label><input type="checkbox" id="category-hierarchical" /> Show hierarchical view</label>
              <label><input type="checkbox" id="category-link" /> Make categories clickable</label>
            </div>

            <div id="option-tags" class="column-option" style="display:none;">
              <label><input type="checkbox" id="tags-link" /> Make tags clickable</label>
              <label>Max tags to show<br>
                <input type="number" id="tags-limit" class="widefat" min="1" max="10" value="5" />
              </label>
            </div>

            <div id="option-rating" class="column-option" style="display:none;">
              <label><input type="checkbox" id="rating-show-count" /> Show number of ratings</label>
              <label><input type="checkbox" id="rating-show-stars" /> Show star icons</label>
            </div>

            <div id="option-sku" class="column-option" style="display:none;">
              <label><input type="checkbox" id="sku-hide-if-empty" /> Hide if empty</label>
              <label><input type="checkbox" id="sku-copyable" /> Add copy button</label>
            </div>

            <div id="option-stock_status" class="column-option" style="display:none;">
              <label><input type="checkbox" id="stock-highlight-low" /> Highlight if low stock</label>
              <label>Low stock threshold<br>
                <input type="number" id="stock-threshold" class="widefat" min="1" value="5" />
              </label>
            </div>
          </div>
          <div class="settings-actions">
            <button type="button" class="button button-primary" id="save-column-settings">
              <span class="dashicons dashicons-yes"></span> Save Settings
            </button>
            <button type="button" class="button" id="cancel-column-settings">
              <span class="dashicons dashicons-no-alt"></span> Cancel
            </button>
          </div>
        </div>
      </div>
      <input type="hidden" name="smarttable_column_layout" id="smarttable_column_layout" value='<?php echo esc_attr(json_encode($layout_data)); ?>'>
    </div>
  </div>
</div>

<div id="smarttable-live-preview" class="smarttable-live-preview postbox">
  <div class="preview-header">
    <h2 class="hndle ui-sortable-handle">👁️ Live Preview</h2>
    <div class="preview-actions">
      <button type="button" id="toggle-preview" class="button">
        <span class="dashicons dashicons-visibility"></span> Toggle
      </button>
      <button type="button" id="refresh-preview" class="button">
        <span class="dashicons dashicons-update"></span> Refresh
      </button>
    </div>
  </div>
  <div class="inside preview-content">
    <p class="description">Real-time preview of your table layout</p>
    <div class="smarttable-preview-table">
      <p class="no-preview-placeholder">Start building your table layout to preview it here.</p>
    </div>
  </div>
</div>

<!-- Import/Export Modal -->
<div id="layout-import-export-modal" class="smarttable-modal" style="display:none;">
  <div class="modal-overlay" id="modal-overlay"></div>
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modal-title">Import/Export Layout</h3>
      <button type="button" class="modal-close" id="modal-close">×</button>
    </div>
    <div class="modal-body">
      <div id="export-content" style="display:none;">
        <p><strong>Copy the JSON below to save your layout:</strong></p>
        <textarea id="export-textarea" class="widefat" rows="10" readonly></textarea>
        <button type="button" class="button button-primary" id="copy-export">
          <span class="dashicons dashicons-admin-page"></span> Copy to Clipboard
        </button>
      </div>
      <div id="import-content" style="display:none;">
        <p><strong>Paste your layout JSON below:</strong></p>
        <textarea id="import-textarea" class="widefat" rows="10" placeholder="Paste JSON here..."></textarea>
        <button type="button" class="button button-primary" id="apply-import">
          <span class="dashicons dashicons-upload"></span> Apply Layout
        </button>
      </div>
    </div>
  </div>
</div>
