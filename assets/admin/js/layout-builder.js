document.addEventListener('DOMContentLoaded', function () {
  const availableList = document.getElementById('available-columns');
  const activeList = document.getElementById('active-columns');
  const layoutInput = document.getElementById('smarttable_column_layout');

  const settingsPanel = document.getElementById('smarttable-column-settings-panel');
  const settingLabel = document.getElementById('column-setting-label');
  const settingMeta = document.getElementById('column-setting-meta');
  const saveSettingsBtn = document.getElementById('save-column-settings');
  const cancelSettingsBtn = document.getElementById('cancel-column-settings');

  if (!availableList || !activeList || !layoutInput) {
    return;
  }

  const stateStore = new WeakMap();
  const columnIconMap = new Map();
  const columnLabelMap = new Map();
  let layoutUpdateScheduled = false;
  let cachedLayoutJSON = '';
  let activeEditingItem = null;

  // Build lookups for icons/labels from the available list
  availableList.querySelectorAll('li[data-type]').forEach((item) => {
    const type = item.dataset.type;
    if (!type) {
      return;
    }
    const iconEl = item.querySelector('.column-icon');
    const iconChar = iconEl ? iconEl.textContent.trim() : '🔧';
    const labelClone = item.cloneNode(true);
    labelClone.querySelectorAll('.drag-handle, .column-icon').forEach((el) => el.remove());
    const labelText = labelClone.textContent.trim();

    columnIconMap.set(type, iconChar || '🔧');
    columnLabelMap.set(type, labelText || type);
  });

  const smartTablePresets = {
    basic: [
      { type: 'image', label: 'Image', options: { size: 'thumbnail' } },
      { type: 'title', label: 'Title', options: { clickable: true } },
      { type: 'price', label: 'Price', options: { show_regular: true, show_sale: true } },
      { type: 'add_to_cart', label: 'Buy', options: { show_qty: false } }
    ],
    comparison: [
      { type: 'title', label: 'Product', options: { clickable: false } },
      { type: 'sku', label: 'SKU', options: {} },
      { type: 'stock_status', label: 'Availability', options: { highlight_low: true } },
      { type: 'custom_field', label: 'Warranty', meta: 'warranty_info', options: { fallback: 'N/A' } }
    ],
    catalog: [
      { type: 'image', label: 'Image', options: { size: 'medium' } },
      { type: 'title', label: 'Name', options: { clickable: true } },
      { type: 'short_description', label: 'Info', options: {} },
      { type: 'price', label: 'Price', options: {} }
    ]
  };

  const presetSelector = document.getElementById('preset-layout-selector');
  const loadPresetBtn = document.getElementById('load-preset-layout');

  if (presetSelector && loadPresetBtn) {
    loadPresetBtn.addEventListener('click', () => {
      const selected = presetSelector.value;
      const preset = smartTablePresets[selected];
      if (!preset) {
        return;
      }

      activeList.innerHTML = '';
      const fragment = document.createDocumentFragment();
      preset.forEach((column) => {
        const item = document.createElement('li');
        buildActiveItem(item, column);
        fragment.appendChild(item);
      });
      activeList.appendChild(fragment);
      scheduleLayoutUpdate(true);
    });
  }

  Sortable.create(availableList, {
    group: {
      name: 'columns',
      pull: 'clone',
      put: false
    },
    sort: false,
    animation: 150,
    onStart: (evt) => evt.item.classList.add('sortable-chosen'),
    onEnd: (evt) => evt.item.classList.remove('sortable-chosen'),
    onChange: (evt) => evt.item.classList.add('sortable-ghost')
  });

  Sortable.create(activeList, {
    group: 'columns',
    animation: 150,
    onSort: () => scheduleLayoutUpdate(),
    onAdd: (evt) => {
      transformToActiveItem(evt.item);
      scheduleLayoutUpdate();
    },
    onStart: (evt) => evt.item.classList.add('sortable-chosen'),
    onEnd: (evt) => evt.item.classList.remove('sortable-chosen'),
    onChange: (evt) => evt.item.classList.add('sortable-ghost')
  });

  function safeParseOptions(value) {
    if (!value) {
      return {};
    }
    if (typeof value === 'object') {
      return value || {};
    }
    try {
      const parsed = JSON.parse(value);
      return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (error) {
      return {};
    }
  }

  function normaliseState(raw) {
    const type = raw.type || '';
    const label = raw.label || columnLabelMap.get(type) || type;
    const meta = raw.meta || '';
    const options = safeParseOptions(raw.options);
    return { type, label, meta, options };
  }

  function setState(item, rawState) {
    const state = normaliseState(rawState);
    stateStore.set(item, state);
    item.dataset.type = state.type;
    item.dataset.label = state.label;
    item.dataset.meta = state.meta;
    item.dataset.options = JSON.stringify(state.options || {});
    return state;
  }

  function getState(item) {
    const existing = stateStore.get(item);
    if (existing) {
      return existing;
    }
    const inferred = {
      type: item.dataset.type || '',
      label: item.dataset.label || item.querySelector('.column-label')?.textContent?.trim() || '',
      meta: item.dataset.meta || '',
      options: safeParseOptions(item.dataset.options)
    };
    return setState(item, inferred);
  }

  function resolveIcon(type) {
    return columnIconMap.get(type) || '🔧';
  }

  function createDragHandle() {
    const handle = document.createElement('span');
    handle.className = 'drag-handle';
    handle.textContent = '☰';
    return handle;
  }

  function createIcon(type) {
    const icon = document.createElement('span');
    icon.className = 'column-icon';
    icon.textContent = resolveIcon(type);
    icon.setAttribute('data-tooltip', 'Column type');
    return icon;
  }

  function createLabelElement(text) {
    const label = document.createElement('span');
    label.className = 'column-label';
    label.textContent = text;
    return label;
  }

  function createSettingsButton(item) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'settings-btn';
    btn.textContent = '⚙';
    btn.setAttribute('data-tooltip', 'Configure column');
    btn.addEventListener('click', (event) => {
      event.stopPropagation();
      openSettingsPanel(item);
    });
    return btn;
  }

  function createRemoveButton(item) {
    const removeIcon = document.createElement('span');
    removeIcon.className = 'remove-column dashicons dashicons-no-alt';
    removeIcon.title = 'Remove column';
    removeIcon.setAttribute('data-tooltip', 'Delete column');
    removeIcon.addEventListener('click', (event) => {
      event.stopPropagation();
      item.remove();
      if (activeEditingItem === item) {
        closeSettingsPanel();
      }
      scheduleLayoutUpdate();
    });
    return removeIcon;
  }

  function buildActiveItem(item, rawState) {
    const state = setState(item, rawState);
    item.classList.add('smarttable-active-column');
    item.classList.remove('sortable-ghost', 'sortable-chosen');
    item.innerHTML = '';

    const fragment = document.createDocumentFragment();
    fragment.appendChild(createDragHandle());
    fragment.appendChild(createIcon(state.type));
    fragment.appendChild(createLabelElement(state.label));
    fragment.appendChild(createSettingsButton(item));
    fragment.appendChild(createRemoveButton(item));

    item.appendChild(fragment);
  }

  function transformToActiveItem(item) {
    const sourceState = {
      type: item.dataset.type || '',
      label: item.dataset.label || columnLabelMap.get(item.dataset.type || '') || item.textContent.trim(),
      meta: item.dataset.meta || '',
      options: safeParseOptions(item.dataset.options)
    };
    buildActiveItem(item, sourceState);
  }

  function openSettingsPanel(item) {
    activeEditingItem = item;
    const state = getState(item);

    settingLabel.value = state.label;
    settingMeta.value = state.meta;
    settingMeta.parentElement.style.display = 'none';

    settingsPanel.querySelectorAll('.column-option').forEach((el) => {
      el.style.display = 'none';
    });

    switch (state.type) {
      case 'price':
        toggleOption('option-price', true);
        document.getElementById('price-show-regular').checked = !!state.options.show_regular;
        document.getElementById('price-show-sale').checked = !!state.options.show_sale;
        break;
      case 'image':
        toggleOption('option-image', true);
        document.getElementById('image-size').value = state.options.size || 'thumbnail';
        break;
      case 'custom_field':
        toggleOption('option-custom_field', true);
        settingMeta.parentElement.style.display = 'block';
        document.getElementById('column-setting-meta-fallback').value = state.options.fallback || '';
        break;
      case 'add_to_cart':
        toggleOption('option-add_to_cart', true);
        document.getElementById('addtocart-show-qty').checked = !!state.options.show_qty;
        break;
      case 'title':
        toggleOption('option-title', true);
        document.getElementById('title-clickable').checked = !!state.options.clickable;
        break;
      case 'category':
        toggleOption('option-category', true);
        document.getElementById('category-hierarchical').checked = !!state.options.hierarchical;
        break;
      case 'tags':
        toggleOption('option-tags', true);
        document.getElementById('tags-link').checked = !!state.options.link;
        break;
      case 'rating':
        toggleOption('option-rating', true);
        document.getElementById('rating-show-count').checked = !!state.options.show_count;
        break;
      case 'sku':
        toggleOption('option-sku', true);
        document.getElementById('sku-hide-if-empty').checked = !!state.options.hide_if_empty;
        break;
      case 'stock_status':
        toggleOption('option-stock_status', true);
        document.getElementById('stock-highlight-low').checked = !!state.options.highlight_low;
        break;
      case 'short_description':
        // No specific options for short description
        break;
      case 'tags':
        toggleOption('option-tags', true);
        document.getElementById('tags-link').checked = !!state.options.link;
        break;
      case 'rating':
        toggleOption('option-rating', true);
        document.getElementById('rating-show-count').checked = !!state.options.show_count;
        break;
      case 'custom_field':
        toggleOption('option-custom_field', true);
        settingMeta.parentElement.style.display = 'block';
        document.getElementById('column-setting-meta-fallback').value = state.options.fallback || '';
        break;
      default:
        break;
    }

    settingsPanel.style.display = 'block';
  }

  function toggleOption(id, show) {
    const el = document.getElementById(id);
    if (el) {
      el.style.display = show ? 'block' : 'none';
    }
  }

  function closeSettingsPanel() {
    activeEditingItem = null;
    settingsPanel.style.display = 'none';
  }

  function collectOptions(type) {
    const opts = {};
    switch (type) {
      case 'price':
        opts.show_regular = document.getElementById('price-show-regular').checked;
        opts.show_sale = document.getElementById('price-show-sale').checked;
        break;
      case 'image':
        opts.size = document.getElementById('image-size').value;
        break;
      case 'custom_field':
        opts.fallback = document.getElementById('column-setting-meta-fallback').value;
        break;
      case 'add_to_cart':
        opts.show_qty = document.getElementById('addtocart-show-qty').checked;
        break;
      case 'title':
        opts.clickable = document.getElementById('title-clickable').checked;
        break;
      case 'category':
        opts.hierarchical = document.getElementById('category-hierarchical').checked;
        break;
      case 'tags':
        opts.link = document.getElementById('tags-link').checked;
        break;
      case 'rating':
        opts.show_count = document.getElementById('rating-show-count').checked;
        break;
      case 'sku':
        opts.hide_if_empty = document.getElementById('sku-hide-if-empty').checked;
        break;
      case 'stock_status':
        opts.highlight_low = document.getElementById('stock-highlight-low').checked;
        break;
      case 'short_description':
        // No options for short description
        break;
      case 'tags':
        opts.link = document.getElementById('tags-link').checked;
        break;
      case 'rating':
        opts.show_count = document.getElementById('rating-show-count').checked;
        break;
      case 'custom_field':
        opts.fallback = document.getElementById('column-setting-meta-fallback').value;
        break;
      default:
        break;
    }
    return opts;
  }

  saveSettingsBtn.addEventListener('click', () => {
    if (!activeEditingItem) {
      return;
    }

    const state = getState(activeEditingItem);
    const updatedState = {
      ...state,
      label: settingLabel.value.trim() || columnLabelMap.get(state.type) || state.type,
      meta: settingMeta.value.trim(),
      options: collectOptions(state.type)
    };

    const normalised = setState(activeEditingItem, updatedState);
    const labelNode = activeEditingItem.querySelector('.column-label');
    if (labelNode) {
      labelNode.textContent = normalised.label;
    }

    scheduleLayoutUpdate();
    closeSettingsPanel();
  });

  cancelSettingsBtn.addEventListener('click', () => {
    closeSettingsPanel();
  });

  function safeParseLayout(value) {
    if (!value) {
      return [];
    }
    try {
      const parsed = JSON.parse(value);
      return Array.isArray(parsed) ? parsed : [];
    } catch (error) {
      return [];
    }
  }

  const initialLayout = safeParseLayout(layoutInput.value);
  if (initialLayout.length) {
    const fragment = document.createDocumentFragment();
    initialLayout.forEach((column) => {
      const item = document.createElement('li');
      buildActiveItem(item, column);
      fragment.appendChild(item);
    });
    activeList.appendChild(fragment);
    cachedLayoutJSON = JSON.stringify(initialLayout);
    renderLivePreview(initialLayout);
  } else {
    cachedLayoutJSON = '[]';
    layoutInput.value = '[]';
  }

  function scheduleLayoutUpdate(forceImmediate = false) {
    if (forceImmediate) {
      layoutUpdateScheduled = false;
      updateLayoutInputImmediate();
      return;
    }

    if (layoutUpdateScheduled) {
      return;
    }
    layoutUpdateScheduled = true;
    requestAnimationFrame(() => {
      layoutUpdateScheduled = false;
      updateLayoutInputImmediate();
    });
  }

  function updateLayoutInputImmediate() {
    const items = activeList.querySelectorAll('li');
    const layout = [];

    items.forEach((item) => {
      const state = getState(item);
      if (!state.type) {
        return;
      }
      const options = state.options && typeof state.options === 'object' ? { ...state.options } : {};
      layout.push({
        type: state.type,
        label: state.label,
        meta: state.meta,
        options
      });
    });

    const serialised = JSON.stringify(layout);
    if (serialised === cachedLayoutJSON) {
      return;
    }

    cachedLayoutJSON = serialised;
    layoutInput.value = serialised;
    renderLivePreview(layout);
  }

  function renderLivePreview(layout) {
    const previewContainer = document.querySelector('.smarttable-preview-table');
    if (!previewContainer) {
      return;
    }

    previewContainer.innerHTML = '';

    if (!layout.length) {
      return;
    }

    const table = document.createElement('table');
    table.className = 'widefat';

    const thead = document.createElement('thead');
    const headerRow = document.createElement('tr');
    layout.forEach((column) => {
      const th = document.createElement('th');
      th.textContent = column.label || column.type;
      headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement('tbody');
    const row = document.createElement('tr');
    layout.forEach((column) => {
      const td = document.createElement('td');
      td.innerHTML = getMockDataForColumn(column);
      row.appendChild(td);
    });
    tbody.appendChild(row);
    table.appendChild(tbody);

    previewContainer.appendChild(table);
  }

  function getMockDataForColumn(column) {
    switch (column.type) {
      case 'image':
        return '<img src="https://via.placeholder.com/60" alt="" />';
      case 'title':
        return '<a href="#">Sample Product</a>';
      case 'price':
        return '$49.99';
      case 'add_to_cart':
        return '<button class="button">Add to Cart</button>';
      case 'sku':
        return 'SKU12345';
      case 'stock_status':
        return '<span style="color:green;">In Stock</span>';
      case 'category':
        return 'Category A, B';
      case 'stock_status':
        return '<span style="color:green;">In Stock</span>';
      case 'short_description':
        return 'Product description...';
      case 'tags':
        return 'Tag1, Tag2';
      case 'rating':
        return '★★★★☆ (4.2)';
      case 'custom_field':
        return column.meta || 'Custom Data';
      default:
        return '—';
    }
  }

  const copyBtn = document.getElementById('smarttable-copy-shortcode-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', () => {
      const shortcodeField = document.getElementById('smarttable-shortcode-field');
      if (!shortcodeField) {
        return;
      }

      const textToCopy = shortcodeField.value;
      if (!navigator.clipboard) {
        shortcodeField.select();
        try {
          if (document.execCommand('copy')) {
            alert('Shortcode copied to clipboard!');
          } else {
            alert('Failed to copy shortcode.');
          }
        } catch (error) {
          alert('Failed to copy shortcode.');
        }
        return;
      }

      navigator.clipboard.writeText(textToCopy).then(() => {
        const originalText = copyBtn.textContent;
        copyBtn.textContent = 'Copied!';
        setTimeout(() => {
          copyBtn.textContent = originalText;
        }, 2000);
      }).catch(() => {
        alert('Failed to copy shortcode.');
      });
    });
  }

  // Style tab functionality
  const styleOptions = document.querySelectorAll('.style-option');
  styleOptions.forEach(option => {
    option.addEventListener('click', function() {
      // Remove selected class from all options
      styleOptions.forEach(opt => opt.classList.remove('selected'));
      // Add selected class to clicked option
      this.classList.add('selected');
      // Check the radio button
      const radio = this.querySelector('input[type="radio"]');
      if (radio) {
        radio.checked = true;
      }
    });
  });

  // Advanced Filter System
  const filterTabs = document.querySelectorAll('.filter-tab');
  const filterPanels = document.querySelectorAll('.filter-panel');
  
  filterTabs.forEach(tab => {
    tab.addEventListener('click', function() {
      const targetTab = this.dataset.tab;
      
      // Remove active class from all tabs and panels
      filterTabs.forEach(t => t.classList.remove('active'));
      filterPanels.forEach(p => p.classList.remove('active'));
      
      // Add active class to clicked tab and corresponding panel
      this.classList.add('active');
      const targetPanel = document.getElementById(targetTab + '-panel');
      if (targetPanel) {
        targetPanel.classList.add('active');
      }
    });
  });

  // Reset Filters
  const resetFiltersBtn = document.getElementById('reset-filters');
  if (resetFiltersBtn) {
    resetFiltersBtn.addEventListener('click', function() {
      if (confirm('Are you sure you want to reset all filters?')) {
        // Reset all select elements
        document.querySelectorAll('.filter-select').forEach(select => {
          select.selectedIndex = 0;
          if (select.multiple) {
            Array.from(select.options).forEach(option => option.selected = false);
          }
        });
        
        // Reset all input elements
        document.querySelectorAll('.filter-input, .price-input, .date-input').forEach(input => {
          input.value = '';
        });
      }
    });
  }

  // Preview Filters
  const previewFiltersBtn = document.getElementById('preview-filters');
  const filterPreview = document.getElementById('filter-preview');
  
  if (previewFiltersBtn && filterPreview) {
    previewFiltersBtn.addEventListener('click', function() {
      filterPreview.style.display = 'block';
      const previewContent = filterPreview.querySelector('.preview-content');
      previewContent.innerHTML = '<p class="preview-loading">Loading preview...</p>';
      
      // Simulate loading (replace with actual AJAX call)
      setTimeout(() => {
        const selectedFilters = [];
        
        // Collect selected categories
        const categories = document.getElementById('smarttable_filter_categories');
        if (categories && categories.selectedOptions.length > 0) {
          const catNames = Array.from(categories.selectedOptions).map(opt => opt.text);
          selectedFilters.push(`Categories: ${catNames.join(', ')}`);
        }
        
        // Collect selected tags
        const tags = document.getElementById('smarttable_filter_tags');
        if (tags && tags.selectedOptions.length > 0) {
          const tagNames = Array.from(tags.selectedOptions).map(opt => opt.text);
          selectedFilters.push(`Tags: ${tagNames.join(', ')}`);
        }
        
        // Show preview
        if (selectedFilters.length > 0) {
          previewContent.innerHTML = `
            <div class="filter-summary">
              <h5>Active Filters:</h5>
              <ul>
                ${selectedFilters.map(filter => `<li>${filter}</li>`).join('')}
              </ul>
              <p><em>Preview functionality will show actual product count in future updates.</em></p>
            </div>
          `;
        } else {
          previewContent.innerHTML = '<p>No filters selected. All products will be displayed.</p>';
        }
      }, 1000);
    });
  }
});
