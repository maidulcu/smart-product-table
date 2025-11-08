# Screenshot Guide for WordPress.org

Create the following 6 screenshots to showcase your plugin's features. These screenshots should match the descriptions in `readme.txt`.

## Required Screenshots

### screenshot-1.png
**Description:** Product table with modern design and filtering options

**What to capture:**
- Frontend view of the product table
- Show products displayed in table format
- Include visible category/tag filters in action
- Display price range filter
- Show search functionality
- Ensure some products are visible with images, prices, and add to cart buttons
- Use a clean, professional theme

**Recommended size:** 1280x720px or higher
**Format:** PNG (recommended) or JPG

---

### screenshot-2.png
**Description:** Admin interface for creating and configuring tables

**What to capture:**
- WordPress admin dashboard
- Product Table edit screen (post type: smarttable_product)
- Show the layout builder metabox
- Display filter options panel
- Show display/style options settings
- Include the shortcode display area

**Recommended size:** 1280x720px or higher
**Format:** PNG (recommended) or JPG

---

### screenshot-3.png
**Description:** Mobile responsive layout with card view

**What to capture:**
- Mobile device view (or browser with mobile dimensions: 375x667px for iPhone)
- Product table in mobile/card layout
- Show how products stack vertically
- Display mobile-friendly filters (if collapsed/expandable)
- Show add to cart buttons are still accessible

**Tips:** Use Chrome DevTools device mode or actual mobile device
**Recommended size:** 375x667px (iPhone) or 414x896px (iPhone Max)
**Format:** PNG (recommended) or JPG

---

### screenshot-4.png
**Description:** Bulk selection and cart functionality

**What to capture:**
- Product table with several products selected (checkboxes checked)
- "Select All" button visible
- "Add Selected to Cart" button prominent
- Show quantity selectors for multiple products
- Display the bulk action interface clearly

**Recommended size:** 1280x720px or higher
**Format:** PNG (recommended) or JPG

---

### screenshot-5.png
**Description:** Multiple design styles available

**What to capture:**
- Either a split view showing different styles side-by-side, OR
- Admin settings showing the style selector dropdown with options like:
  - Standard Grid
  - Compact List
  - Product Cards
  - Striped Rows
  - Modern Shop
- If showing frontend, pick the most visually distinct style

**Recommended size:** 1280x720px or higher
**Format:** PNG (recommended) or JPG

---

### screenshot-6.png
**Description:** Advanced filtering and search options

**What to capture:**
- Product table with filter panel fully visible
- Show all available filters:
  - Category dropdown (expanded if possible)
  - Tag selector
  - Price range slider or input
  - Sort by dropdown
- Search bar with example text
- "Apply Filters" and "Reset" buttons visible
- Show filtered results if possible

**Recommended size:** 1280x720px or higher
**Format:** PNG (recommended) or JPG

---

## General Screenshot Tips

1. **Clean Environment**
   - Use a fresh WordPress installation with minimal plugins
   - Use a clean, professional theme (Twenty Twenty-Four, Astra, or similar)
   - Add sample WooCommerce products with good images

2. **Image Quality**
   - Use high resolution (at least 1280x720px for desktop)
   - Use PNG format for crisp text and UI elements
   - Compress images to keep file size under 1MB each
   - Ensure good contrast and readability

3. **Professional Appearance**
   - Hide browser bookmarks bar
   - Close unnecessary tabs
   - Use full-screen or remove browser chrome
   - Ensure consistent styling across screenshots

4. **What to Avoid**
   - Demo/test content with Lorem Ipsum
   - Broken images or placeholder graphics
   - Personal/confidential information
   - Other plugin promotions in view
   - Browser developer tools visible
   - Obvious staging/development URLs

## Tools for Taking Screenshots

- **macOS:** Cmd+Shift+4 (then Space for full window)
- **Windows:** Windows+Shift+S or Snipping Tool
- **Chrome DevTools:** Device mode for mobile screenshots
- **Firefox:** Built-in screenshot tool (Ctrl+Shift+S)
- **Third-party:** Awesome Screenshot, Nimbus, Lightshot

## After Creating Screenshots

1. Save all 6 screenshots in this `.wordpress-org/` directory
2. Name them exactly: `screenshot-1.png` through `screenshot-6.png`
3. Optimize file sizes using ImageOptim, TinyPNG, or similar
4. Verify they match the descriptions in `readme.txt`
5. Test by viewing them at different sizes

## WordPress.org Upload

When submitting to WordPress.org SVN:
```bash
svn co https://plugins.svn.wordpress.org/smart-product-table
cd smart-product-table
mkdir -p assets
cp .wordpress-org/screenshot-*.png assets/
svn add assets/screenshot-*.png
svn commit -m "Add plugin screenshots"
```

The screenshots will appear on your plugin page at:
`https://wordpress.org/plugins/smart-product-table/`
