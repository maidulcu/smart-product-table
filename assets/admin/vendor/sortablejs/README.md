# SortableJS Library

This directory should contain the SortableJS library files for the admin interface drag-and-drop functionality.

## Required Files

Download the following file from the SortableJS CDN or GitHub:

1. **Sortable.min.js** - Main JavaScript file

## Option 1: Download from jsDelivr CDN

```bash
# Navigate to this directory
cd assets/admin/vendor/sortablejs

# Download JavaScript
curl -L -o Sortable.min.js "https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"
```

## Option 2: Download from GitHub

Visit: https://github.com/SortableJS/Sortable/releases/tag/1.15.0

Download the release and extract:
- `Sortable.min.js` → `Sortable.min.js`

## Option 3: Use npm

```bash
npm install sortablejs@1.15.0
cp node_modules/sortablejs/Sortable.min.js ./
```

## License

SortableJS is licensed under the MIT License.
See: https://github.com/SortableJS/Sortable/blob/master/LICENSE

## Why Local?

WordPress.org requires all third-party libraries to be bundled with the plugin rather than loaded from external CDNs. This ensures:
- Better performance
- Reliability (no dependency on external services)
- Security (no external requests)
- Compliance with WordPress.org guidelines

## After Download

Once the file is in place, the plugin will automatically use the local file instead of the CDN version.
