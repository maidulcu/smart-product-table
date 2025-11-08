# Choices.js Library

This directory should contain the Choices.js library files for the admin interface.

## Required Files

Download the following files from the Choices.js CDN or GitHub:

1. **choices.min.js** - Main JavaScript file
2. **choices.min.css** - Stylesheet file

## Option 1: Download from jsDelivr CDN

```bash
# Navigate to this directory
cd assets/admin/vendor/choices

# Download JavaScript
curl -L -o choices.min.js "https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/scripts/choices.min.js"

# Download CSS
curl -L -o choices.min.css "https://cdn.jsdelivr.net/npm/choices.js@10.2.0/public/assets/styles/choices.min.css"
```

## Option 2: Download from GitHub

Visit: https://github.com/Choices-js/Choices/releases/tag/v10.2.0

Download the release and extract:
- `public/assets/scripts/choices.min.js` → `choices.min.js`
- `public/assets/styles/choices.min.css` → `choices.min.css`

## Option 3: Use npm

```bash
npm install choices.js@10.2.0
cp node_modules/choices.js/public/assets/scripts/choices.min.js ./
cp node_modules/choices.js/public/assets/styles/choices.min.css ./
```

## License

Choices.js is licensed under the MIT License.
See: https://github.com/Choices-js/Choices/blob/master/LICENSE

## Why Local?

WordPress.org requires all third-party libraries to be bundled with the plugin rather than loaded from external CDNs. This ensures:
- Better performance
- Reliability (no dependency on external services)
- Security (no external requests)
- Compliance with WordPress.org guidelines

## After Download

Once files are in place, the plugin will automatically use these local files instead of the CDN version.
