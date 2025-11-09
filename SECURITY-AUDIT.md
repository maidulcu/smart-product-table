# Security & WordPress Coding Standards Audit Report

**Plugin:** Smart Product Table v1.0.0
**Date:** 2025-11-09
**Status:** PRE-SUBMISSION AUDIT

---

## CRITICAL SECURITY ISSUES (Must Fix Before Submission)

### 1. ❌ CSRF Vulnerability - Missing Nonce Verification

**File:** `src/Admin/LayoutBuilderMetabox.php`
**Lines:** 150-199 (save_layout function)
**Severity:** 🔴 CRITICAL

**Issue:**
The `save_layout()` function saves $_POST data without verifying a nonce. This allows Cross-Site Request Forgery (CSRF) attacks where an attacker could trick an admin into saving malicious data.

**Current Code:**
```php
public function save_layout($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if ($post->post_type !== 'smarttable_product') return;

    // NO NONCE VERIFICATION HERE!
    $layout_json = isset($_POST['smarttable_column_layout']) ? ...
```

**Fix Required:**
```php
// Add nonce field in metabox render
wp_nonce_field('smarttable_save_layout', 'smarttable_layout_nonce');

// Verify nonce in save function
if (!isset($_POST['smarttable_layout_nonce']) ||
    !wp_verify_nonce($_POST['smarttable_layout_nonce'], 'smarttable_save_layout')) {
    return;
}
```

**WordPress.org Impact:** Will be REJECTED

---

### 2. ❌ Nonce Name Mismatches in AJAX Handlers

**Files:**
- `src/Ajax/BulkCartHandler.php` (line 16)
- `src/Ajax/FilterHandler.php` (line 18)

**Severity:** 🔴 HIGH

**Issue:**
AJAX handlers verify nonces that don't match the nonces created in `src/Core/Plugin.php`.

**Mismatches:**
| Handler | Expects | Plugin Creates | Status |
|---------|---------|----------------|--------|
| BulkCartHandler | `smarttable_bulk_cart` | `smarttable_nonce` | ❌ FAIL |
| FilterHandler | `smarttable_filter` | `smarttable_nonce` | ❌ FAIL |
| PaginationHandler | `smarttable_nonce` | `smarttable_nonce` | ✅ OK |
| TableAjaxHandler | `smarttable_load_table` | `smarttable_nonce` | ❌ FAIL |

**Fix Required:**
Either:
1. Update Plugin.php to create multiple nonces for each action, OR
2. Update handlers to use the same `smarttable_nonce`

**WordPress.org Impact:** Functionality broken, reviewers will test and fail

---

### 3. ⚠️ Unsanitized JSON Storage

**File:** `src/Admin/LayoutBuilderMetabox.php`
**Line:** 161
**Severity:** 🟡 MEDIUM

**Issue:**
```php
$layout_json = isset($_POST['smarttable_column_layout']) ?
    wp_unslash($_POST['smarttable_column_layout']) : '';
update_post_meta($post_id, '_smarttable_column_layout', $layout_json);
```

The JSON data is unslashed but not validated before storage. Could allow malicious JSON.

**Fix Required:**
```php
$layout_json = isset($_POST['smarttable_column_layout']) ?
    wp_unslash($_POST['smarttable_column_layout']) : '';

// Validate JSON
$decoded = json_decode($layout_json, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    update_post_meta($post_id, '_smarttable_column_layout', $layout_json);
}
```

---

### 4. ⚠️ Missing Output Escaping

**File:** `src/CPT/Post_Type_Product.php`
**Line:** 74
**Severity:** 🟡 MEDIUM

**Issue:**
```php
echo '[smart_product_table id="' . absint( $post_id ) . '"]';
```

Should be:
```php
echo esc_html('[smart_product_table id="' . absint( $post_id ) . '"]');
```

---

## WORDPRESS.ORG COMPLIANCE ISSUES

### 5. ⚠️ External CDN Dependencies

**File:** `src/Admin/LayoutBuilderMetabox.php`
**Lines:** 65-71
**Severity:** 🟡 MEDIUM

**Issue:**
Loading SortableJS from external CDN:
```php
wp_enqueue_script(
    'sortable-js',
    'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js',
    ...
);
```

**WordPress.org Requirement:** All third-party libraries must be bundled locally.

**Fix Required:** Download and bundle SortableJS locally, similar to Choices.js setup.

---

### 6. ⚠️ Debug Logging in Production

**Files:**
- `src/Ajax/FilterHandler.php` (lines 27-29, 75)
- `src/Admin/LayoutBuilderMetabox.php` (lines 157-158)

**Severity:** 🟡 LOW

**Issue:**
```php
error_log('SmartTable Filter Request - Post ID: ' . $post_id);
```

Should be:
```php
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('SmartTable Filter Request - Post ID: ' . $post_id);
}
```

---

## WORDPRESS CODING STANDARDS

### Code Quality Issues

#### 1. Direct $_POST Access in Conditionals

**File:** `src/Admin/LayoutBuilderMetabox.php`
**Line:** 177

```php
// Current (incorrect)
update_post_meta($post_id, '_smarttable_tax_query_relation',
    in_array($_POST['smarttable_tax_query_relation'] ?? '', ['AND', 'OR'], true) ?
    $_POST['smarttable_tax_query_relation'] : 'AND');

// Should be
$tax_relation = sanitize_text_field($_POST['smarttable_tax_query_relation'] ?? 'AND');
$tax_relation = in_array($tax_relation, ['AND', 'OR'], true) ? $tax_relation : 'AND';
update_post_meta($post_id, '_smarttable_tax_query_relation', $tax_relation);
```

#### 2. Missing Function Documentation

Many functions lack proper PHPDoc blocks with `@param` and `@return` tags.

#### 3. Inline Scripts Without Proper Escaping

**File:** `src/Admin/LayoutBuilderMetabox.php` (lines 114-137)

Inline JavaScript should be in separate file or use `wp_add_inline_script()`.

---

## SECURITY BEST PRACTICES ✅

### What's Done Right:

1. ✅ **ABSPATH checks** - All files include security check
2. ✅ **Nonce usage** - Most AJAX handlers use nonces (though names need fixing)
3. ✅ **Capability checks** - `current_user_can()` used in save functions
4. ✅ **Data escaping** - 96 instances of `esc_*` functions found
5. ✅ **Data sanitization** - 47 instances of `sanitize_*` functions found
6. ✅ **Prepared queries** - Using WordPress APIs (wc_get_products, WP_Query)
7. ✅ **No eval() or base64_decode()** - Clean code
8. ✅ **Internationalization** - Proper use of translation functions

---

## SQL INJECTION RISK ASSESSMENT

### ✅ NO SQL INJECTION VULNERABILITIES FOUND

**Analysis:**
- All database queries use WordPress/WooCommerce APIs
- `WP_Query` and `wc_get_products()` use prepared statements internally
- `get_post_meta()` and `update_post_meta()` are safe
- No direct `$wpdb->query()` calls with user input

**Examples of Safe Usage:**
```php
// Safe - using WordPress API
$query = new \WP_Query($query_args);

// Safe - using WooCommerce API
$products = wc_get_products($args);

// Safe - WordPress handles escaping
update_post_meta($post_id, '_smarttable_design_style', $design_style);
```

---

## XSS RISK ASSESSMENT

### ⚠️ MINOR XSS RISKS (All Fixable)

**Risk Areas:**
1. Admin shortcode display (line 74 in Post_Type_Product.php) - MEDIUM
2. Inline scripts in metabox (LayoutBuilderMetabox.php) - LOW

**Frontend Output:** ✅ SAFE
- All product data rendered through `wp_kses_post()` or `esc_html()`
- Price HTML uses `wp_kses_post()` which allows safe WooCommerce markup
- All attributes use `esc_attr()`
- All URLs use `esc_url()`

---

## PRIORITY FIX LIST

### Must Fix Before WordPress.org Submission:

1. **🔴 CRITICAL:** Add nonce verification to LayoutBuilderMetabox save function
2. **🔴 HIGH:** Fix nonce name mismatches in AJAX handlers
3. **🟡 MEDIUM:** Bundle SortableJS locally (WordPress.org compliance)
4. **🟡 MEDIUM:** Add JSON validation before storage
5. **🟡 MEDIUM:** Escape shortcode output in admin column
6. **🟡 LOW:** Wrap debug logging in WP_DEBUG checks
7. **🟡 LOW:** Clean up coding standards (PHPDoc, direct $_POST usage)

---

## TESTING RECOMMENDATIONS

### Security Testing:

- [ ] Test CSRF protection on metabox saves
- [ ] Test all AJAX handlers with correct nonces
- [ ] Test XSS prevention on all frontend outputs
- [ ] Test capability checks for admin functions
- [ ] Test with WP_DEBUG enabled (no errors/warnings)
- [ ] Test with Query Monitor plugin (check for queries)

### Browser Testing:

- [ ] Test admin metabox in Chrome, Firefox, Safari
- [ ] Test frontend table in all major browsers
- [ ] Test mobile responsive layouts

---

## ESTIMATED TIME TO FIX

- Critical issues (1-2): **2-3 hours**
- Medium issues (3-5): **1-2 hours**
- Low issues (6-7): **1 hour**

**Total:** 4-6 hours of development + testing

---

## CONCLUSION

**Overall Security Grade:** 🟡 B- (Good, but needs critical fixes)

**WordPress.org Readiness:** ❌ NOT READY

**Blocking Issues:**
1. Missing CSRF protection in metabox
2. AJAX nonce mismatches (broken functionality)
3. External CDN dependency (SortableJS)

**Recommendation:**
Fix critical and high-priority issues before submission. The codebase shows good security practices overall, but the CSRF vulnerability and nonce mismatches must be resolved.

---

**Audited by:** Claude (Automated Security Review)
**Next Review:** After fixes are implemented
