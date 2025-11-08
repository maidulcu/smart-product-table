# WordPress.org Plugin Assets

This directory contains the graphical assets for the WordPress.org plugin repository.

## Required Assets

### Plugin Banner
- **banner-772x250.png** - Standard resolution banner (required)
- **banner-1544x500.png** - High resolution banner for retina displays (recommended)

### Plugin Icon
- **icon-128x128.png** - Standard resolution icon (required)
- **icon-256x256.png** - High resolution icon for retina displays (recommended)

### Screenshots
Place screenshots in the root `.wordpress-org/` directory:
- **screenshot-1.png** - Product table with modern design and filtering options
- **screenshot-2.png** - Admin interface for creating and configuring tables
- **screenshot-3.png** - Mobile responsive layout with card view
- **screenshot-4.png** - Bulk selection and cart functionality
- **screenshot-5.png** - Multiple design styles available
- **screenshot-6.png** - Advanced filtering and search options

## Image Specifications

### Banner Guidelines
- Use high-quality images (PNG or JPG)
- Keep text minimal and readable
- Standard: 772x250px
- Retina: 1544x500px (2x)
- File size: Keep under 500KB

### Icon Guidelines
- Use a simple, recognizable design
- Works well at small sizes
- Standard: 128x128px
- Retina: 256x256px (2x)
- File size: Keep under 100KB
- Transparent background recommended

### Screenshot Guidelines
- Use actual plugin screenshots
- Show key features clearly
- Recommended size: 1280x720px or larger
- File format: PNG or JPG
- File size: Keep under 1MB each
- Match the order described in readme.txt

## How to Add Assets

1. Create your images according to the specifications above
2. Save them in this `.wordpress-org/` directory
3. When submitting to WordPress.org SVN, these will be placed in the `/assets` directory of your SVN repository

## SVN Structure

```
/assets/
  banner-772x250.png
  banner-1544x500.png
  icon-128x128.png
  icon-256x256.png
  screenshot-1.png
  screenshot-2.png
  screenshot-3.png
  screenshot-4.png
  screenshot-5.png
  screenshot-6.png
/trunk/
  (your plugin files)
/tags/
  /1.0.0/
    (release files)
```

## Tools for Creating Assets

- **Figma** - https://www.figma.com
- **Canva** - https://www.canva.com
- **Photoshop/GIMP** - For professional designs
- **Screenshot tools** - Built-in OS screenshot tools
- **ImageOptim** - For optimizing file sizes

## Notes

- Assets are NOT included in the plugin ZIP file
- They are stored separately in the SVN `/assets` directory
- Update assets anytime without releasing a new version
- Follow WordPress.org branding guidelines
