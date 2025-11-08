# WordPress.org Submission Checklist

This checklist ensures your Smart Product Table plugin is ready for WordPress.org submission.

## Pre-Submission Requirements

### ✅ Code Quality

- [x] All PHP files start with `<?php` and include security checks (`if ( ! defined( 'ABSPATH' ) ) exit;`)
- [x] Proper nonce verification for AJAX requests
- [x] Data sanitization using `sanitize_*()` functions (47 instances found)
- [x] Output escaping using `esc_*()` functions (96 instances found)
- [x] No eval() or base64_decode() in production code
- [x] No direct database queries (use $wpdb or WordPress functions)
- [x] Proper internationalization (text domain: `smart-product-table`)

### ✅ Required Files

- [x] `readme.txt` - WordPress.org formatted readme
- [x] `LICENSE` or license headers (GPL v2 or later)
- [x] Main plugin file: `smart-product-table.php`
- [x] `uninstall.php` - Cleanup on uninstall
- [x] Translation file: `languages/smart-product-table.pot`
- [x] `.gitignore` - For development
- [x] `.svnignore` - For WordPress.org SVN

### ⚠️ Assets Needed (Before Submission)

- [ ] `screenshot-1.png` - Product table with filtering
- [ ] `screenshot-2.png` - Admin interface
- [ ] `screenshot-3.png` - Mobile responsive layout
- [ ] `screenshot-4.png` - Bulk selection functionality
- [ ] `screenshot-5.png` - Multiple design styles
- [ ] `screenshot-6.png` - Advanced filtering options
- [ ] `banner-772x250.png` - Plugin banner (standard)
- [ ] `banner-1544x500.png` - Plugin banner (retina, optional)
- [ ] `icon-128x128.png` - Plugin icon (standard)
- [ ] `icon-256x256.png` - Plugin icon (retina, optional)

**See:** `.wordpress-org/SCREENSHOTS.md` for detailed screenshot guidelines

### ⚠️ Third-Party Libraries

- [ ] Download Choices.js locally (run `./download-vendor-assets.sh`)
- [x] Choices.js license file included
- [x] Code uses local files when available, CDN as fallback
- [x] Third-party resources disclosed in readme.txt

**Action Required:**
```bash
# Run this script to download vendor assets
./download-vendor-assets.sh

# Or manually download from:
# https://github.com/Choices-js/Choices/releases/tag/v10.2.0
# Place files in: assets/admin/vendor/choices/
```

### ✅ Plugin Headers

Ensure these are correctly set in `smart-product-table.php`:

```php
Plugin Name: Smart Product Table
Version: 1.0.0
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Text Domain: smart-product-table
License: GPL v2 or later
```

### ✅ readme.txt Requirements

- [x] Plugin name in header
- [x] Contributors (WordPress.org username)
- [x] Donate link (optional, can be GitHub)
- [x] Tags (max 12, we have 5)
- [x] Requires at least (WordPress version)
- [x] Tested up to (latest WordPress version: 6.7)
- [x] Stable tag (matches Version in main file)
- [x] License and License URI
- [x] Short description
- [x] Long description with features
- [x] Installation instructions
- [x] FAQ section
- [x] Screenshots description (6 screenshots)
- [x] Changelog
- [x] Upgrade notice

## Testing Requirements

### Compatibility Testing

- [ ] Test on WordPress 6.7 (latest)
- [ ] Test on WordPress 5.0 (minimum required)
- [ ] Test on WooCommerce 8.0+ (latest)
- [ ] Test on WooCommerce 3.0 (minimum required)
- [ ] Test with PHP 7.4
- [ ] Test with PHP 8.0
- [ ] Test with PHP 8.1
- [ ] Test with PHP 8.2
- [ ] Test with PHP 8.3

### Browser Testing

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers (iOS Safari, Chrome Android)

### Functionality Testing

- [ ] Plugin activation works without errors
- [ ] Plugin deactivation works without errors
- [ ] Uninstall removes all data (test via WP-CLI or manually)
- [ ] Create new product table
- [ ] Edit existing product table
- [ ] Shortcode displays correctly
- [ ] AJAX filtering works
- [ ] AJAX pagination works
- [ ] Bulk add to cart works
- [ ] Mobile responsive view works
- [ ] All design styles render correctly
- [ ] Search functionality works
- [ ] No JavaScript console errors
- [ ] No PHP errors/warnings (with WP_DEBUG enabled)

### Performance Testing

- [ ] Test with 100+ products
- [ ] Test with 1000+ products
- [ ] Page load time is acceptable
- [ ] No N+1 query problems
- [ ] Caching compatibility (test with WP Super Cache or W3 Total Cache)

### Security Testing

- [ ] AJAX requests use nonces
- [ ] User capabilities are checked
- [ ] All inputs are sanitized
- [ ] All outputs are escaped
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] No CSRF vulnerabilities
- [ ] File upload security (if applicable)

## Code Standards

### WordPress Coding Standards

Run PHP CodeSniffer with WordPress ruleset:

```bash
# Install PHPCS and WordPress standards
composer global require "squizlabs/php_codesniffer=*"
composer global require wp-coding-standards/wpcs

# Configure PHPCS
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs

# Run PHPCS
phpcs --standard=WordPress --extensions=php --ignore=vendor/ .

# Fix auto-fixable issues
phpcbf --standard=WordPress --extensions=php --ignore=vendor/ .
```

Expected standards:
- [ ] No critical errors
- [ ] Minimal warnings (document any unavoidable warnings)

## WordPress.org Submission Process

### 1. Prepare Plugin Package

```bash
# Ensure you're in the plugin directory
cd /path/to/smart-product-table

# Download vendor assets
./download-vendor-assets.sh

# Remove development files
rm -rf .git .github node_modules

# Create plugin ZIP
zip -r smart-product-table.zip . -x "*.git*" "*.DS_Store" "node_modules/*" ".wordpress-org/*" "*.md"
```

### 2. Submit to WordPress.org

1. Create account at https://wordpress.org (if you don't have one)
2. Go to https://wordpress.org/plugins/developers/add/
3. Upload `smart-product-table.zip`
4. Wait for automated checks (usually instant)
5. Review will be done by WordPress.org team (typically 1-14 days)

### 3. Respond to Review

If changes are requested:
- Address all feedback from reviewers
- Make necessary code changes
- Reply to the review email
- Upload updated plugin if needed

### 4. SVN Setup (After Approval)

Once approved, you'll get SVN access:

```bash
# Checkout SVN repository
svn co https://plugins.svn.wordpress.org/smart-product-table svn-smart-product-table
cd svn-smart-product-table

# Directory structure:
# /trunk/        - Development version
# /tags/         - Released versions
# /assets/       - Screenshots, banners, icons

# Add plugin files to trunk
cp -r /path/to/smart-product-table/* trunk/
cd trunk
svn add --force * --auto-props --parents --depth infinity -q
svn commit -m "Initial commit of Smart Product Table"

# Add assets
cd ../assets
cp /path/to/.wordpress-org/screenshot-*.png .
cp /path/to/.wordpress-org/banner-*.png .
cp /path/to/.wordpress-org/icon-*.png .
svn add *.png
svn commit -m "Add plugin assets"

# Create first release tag
cd ..
svn cp trunk tags/1.0.0
svn commit -m "Tagging version 1.0.0"
```

### 5. Post-Submission

- [ ] Verify plugin appears on WordPress.org
- [ ] Test installation from WordPress.org
- [ ] Set up plugin support forum monitoring
- [ ] Plan for future updates and support

## Common Rejection Reasons (Avoid These!)

1. **Security Issues**
   - Missing nonce verification
   - Unescaped output
   - Unsanitized input
   - Direct database access without $wpdb

2. **Code Quality**
   - Using deprecated WordPress functions
   - Not following WordPress coding standards
   - Including minified files without source
   - Obfuscated code

3. **Licensing**
   - Incompatible license (must be GPL-compatible)
   - Missing license information
   - Using non-GPL libraries

4. **Functionality**
   - "Phoning home" without disclosure
   - Including advertising without clear disclosure
   - Bundling other plugins
   - Calling external services without user consent

5. **Naming**
   - Using "WordPress" or "Plugin" in slug
   - Trademarked names without permission
   - Too generic plugin name

## Resources

- [Plugin Developer Handbook](https://developer.wordpress.org/plugins/)
- [Plugin Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)
- [Common Rejection Reasons](https://developer.wordpress.org/plugins/wordpress-org/common-issues/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [Plugin Security](https://developer.wordpress.org/plugins/security/)

## Support After Launch

Plan for:
- Monitoring WordPress.org support forums
- Regular updates for WordPress/WooCommerce compatibility
- Security patches
- Feature requests from users
- Bug fixes
- Translation support

## Notes

- First review typically takes 5-14 days
- Be patient and professional with reviewers
- Address all feedback completely
- Keep plugin updated after launch
- Respond to support requests promptly

Good luck with your submission! 🚀
