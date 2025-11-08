(function($){
    $(document).ready(function(){

        // AJAX pagination handler
        $(document).on('click', '.smarttable-page-link', function(e){
            e.preventDefault();
            
            var $link = $(this);
            var $wrapper = $link.closest('.smarttable-wrapper');
            var $pagination = $link.closest('.smarttable-pagination');
            var page = $link.data('page');
            
            if (!page || $wrapper.hasClass('loading')) return;
            
            // Show loading
            $wrapper.addClass('loading');
            
            // Get pagination data
            var data = {
                action: 'smarttable_load_page',
                nonce: smarttable_ajax_object.nonce,
                page: page,
                post_id: $pagination.data('post-id') || 0,
                columns: $pagination.data('columns'),
                limit: $pagination.data('limit'),
                category: $pagination.data('category'),
                tag: $pagination.data('tag'),
                filter_categories: $pagination.data('filter-categories') || '',
                filter_tags: $pagination.data('filter-tags') || '',
                orderby: $pagination.data('orderby'),
                order: $pagination.data('order')
            };
            
            $.ajax({
                url: smarttable_ajax_object.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $wrapper.replaceWith(response.data.html);
                    }
                },
                error: function() {
                    alert('Error loading page');
                },
                complete: function() {
                    $wrapper.removeClass('loading');
                }
            });
        });
        
        // Select all functionality
        $(document).on('change', '.smarttable-select-all', function(){
            var $wrapper = $(this).closest('.smarttable-wrapper');
            var isChecked = $(this).is(':checked');
            $wrapper.find('.smarttable-product-select').prop('checked', isChecked);
            updateBulkActions($wrapper);
        });
        
        // Individual product select
        $(document).on('change', '.smarttable-product-select', function(){
            var $wrapper = $(this).closest('.smarttable-wrapper');
            updateBulkActions($wrapper);
        });
        
        // Apply filters
        $(document).on('click', '.smarttable-filter-btn', function(e){
            e.preventDefault();
            var $wrapper = $(this).closest('.smarttable-wrapper');
            applyFilters($wrapper);
        });
        
        // Clear filters
        $(document).on('click', '.smarttable-clear-btn', function(e){
            e.preventDefault();
            var $wrapper = $(this).closest('.smarttable-wrapper');
            var $form = $wrapper.find('.smarttable-filter-form');
            $form.find('select, input').val('');
            applyFilters($wrapper);
        });
        
        function applyFilters($wrapper) {
            var $form = $wrapper.find('.smarttable-filter-form');
            var $pagination = $wrapper.find('.smarttable-pagination');
            
            // Show loading
            $wrapper.addClass('loading');
            
            // Get filter values
            var filterCategory = $form.find('[name="filter_category"]').val();
            var minPrice = $form.find('[name="min_price"]').val();
            var maxPrice = $form.find('[name="max_price"]').val();
            
            // Get pagination data
            var data = {
                action: 'smarttable_load_page',
                nonce: smarttable_ajax_object.nonce,
                page: 1, // Reset to first page
                post_id: $pagination.data('post-id') || 0,
                columns: $pagination.data('columns'),
                limit: $pagination.data('limit'),
                category: $pagination.data('category'),
                tag: $pagination.data('tag'),
                filter_categories: $pagination.data('filter-categories') || '',
                filter_tags: $pagination.data('filter-tags') || '',
                orderby: $pagination.data('orderby'),
                order: $pagination.data('order'),
                // Add filter parameters
                filter_category: filterCategory,
                min_price: minPrice,
                max_price: maxPrice
            };
            
            $.ajax({
                url: smarttable_ajax_object.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $wrapper.replaceWith(response.data.html);
                    }
                },
                error: function() {
                    alert('Error applying filters');
                },
                complete: function() {
                    $wrapper.removeClass('loading');
                }
            });
        }
        
        // Bulk add to cart
        $(document).on('click', '.smarttable-bulk-cart', function(){
            var $wrapper = $(this).closest('.smarttable-wrapper');
            var selectedIds = [];
            
            $wrapper.find('.smarttable-product-select:checked').each(function(){
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) return;
            
            // Add to cart via AJAX
            $.ajax({
                url: smarttable_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'smarttable_bulk_add_to_cart',
                    nonce: smarttable_ajax_object.nonce,
                    product_ids: selectedIds
                },
                success: function(response) {
                    if (response.success) {
                        alert('Products added to cart!');
                        // Uncheck all
                        $wrapper.find('.smarttable-product-select, .smarttable-select-all').prop('checked', false);
                        updateBulkActions($wrapper);
                    } else {
                        alert('Error adding products to cart');
                    }
                }
            });
        });
        
        function updateBulkActions($wrapper) {
            var selectedCount = $wrapper.find('.smarttable-product-select:checked').length;
            var $bulkBtn = $wrapper.find('.smarttable-bulk-cart');
            var $countSpan = $wrapper.find('.selected-count');
            
            $countSpan.text(selectedCount + ' selected');
            $bulkBtn.prop('disabled', selectedCount === 0);
            
            // Update select all checkbox
            var totalCount = $wrapper.find('.smarttable-product-select').length;
            var $selectAll = $wrapper.find('.smarttable-select-all');
            $selectAll.prop('indeterminate', selectedCount > 0 && selectedCount < totalCount);
            $selectAll.prop('checked', selectedCount === totalCount && totalCount > 0);
        }

    });
})(jQuery);