#!/bin/bash

# Download Vendor Assets Script
# This script downloads required third-party libraries for the Smart Product Table plugin

set -e

echo "========================================="
echo "Smart Product Table - Vendor Asset Setup"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Choices.js setup
CHOICES_DIR="assets/admin/vendor/choices"
CHOICES_VERSION="10.2.0"

echo "Setting up Choices.js v${CHOICES_VERSION}..."

# Create directory if it doesn't exist
mkdir -p "$CHOICES_DIR"

# Download Choices.js files
echo "Downloading choices.min.js..."
if curl -L -f -o "${CHOICES_DIR}/choices.min.js" "https://cdn.jsdelivr.net/npm/choices.js@${CHOICES_VERSION}/public/assets/scripts/choices.min.js" 2>/dev/null; then
    echo -e "${GREEN}✓${NC} choices.min.js downloaded successfully"
else
    echo -e "${YELLOW}! Could not download from CDN. Trying alternative method...${NC}"

    # Try with wget
    if command -v wget &> /dev/null; then
        wget -q -O "${CHOICES_DIR}/choices.min.js" "https://cdn.jsdelivr.net/npm/choices.js@${CHOICES_VERSION}/public/assets/scripts/choices.min.js" && \
        echo -e "${GREEN}✓${NC} choices.min.js downloaded successfully" || \
        echo -e "${YELLOW}! Failed to download. Please download manually from: https://github.com/Choices-js/Choices/releases${NC}"
    else
        echo -e "${YELLOW}! Please install curl or wget, or download manually from: https://github.com/Choices-js/Choices/releases${NC}"
    fi
fi

echo "Downloading choices.min.css..."
if curl -L -f -o "${CHOICES_DIR}/choices.min.css" "https://cdn.jsdelivr.net/npm/choices.js@${CHOICES_VERSION}/public/assets/styles/choices.min.css" 2>/dev/null; then
    echo -e "${GREEN}✓${NC} choices.min.css downloaded successfully"
else
    echo -e "${YELLOW}! Could not download from CDN. Trying alternative method...${NC}"

    # Try with wget
    if command -v wget &> /dev/null; then
        wget -q -O "${CHOICES_DIR}/choices.min.css" "https://cdn.jsdelivr.net/npm/choices.js@${CHOICES_VERSION}/public/assets/styles/choices.min.css" && \
        echo -e "${GREEN}✓${NC} choices.min.css downloaded successfully" || \
        echo -e "${YELLOW}! Failed to download. Please download manually from: https://github.com/Choices-js/Choices/releases${NC}"
    else
        echo -e "${YELLOW}! Please install curl or wget, or download manually from: https://github.com/Choices-js/Choices/releases${NC}"
    fi
fi

echo ""
echo "========================================="
echo "Vendor asset setup complete!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Verify files exist in ${CHOICES_DIR}/"
echo "2. Run: ls -lh ${CHOICES_DIR}/"
echo "3. Test the plugin in your WordPress admin"
echo ""
