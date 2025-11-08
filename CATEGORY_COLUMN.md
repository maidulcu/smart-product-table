# New Columns Implementation

## Overview
আপনার Smart Product Table এ এখন ৫টি নতুন column যোগ করা হয়েছে:
- **Category** - Product categories
- **Stock Status** - Stock availability
- **Short Description** - Product short description
- **Tags** - Product tags
- **Rating** - Product ratings with stars
- **Custom Field** - Any custom meta field value

## Usage

### Shortcode এ নতুন Columns ব্যবহার করুন:

```
[smarttable_simple columns="image,title,category,stock_status,short_description,tags,rating,price,add_to_cart"]
```

### নির্দিষ্ট Column দেখানোর জন্য:

```
[smarttable_simple columns="title,stock_status,rating,price"]
[smarttable_simple columns="title,tags,short_description"]
[smarttable_simple columns="title,custom_field,price"]
```

### Admin Panel এ:
1. WordPress Admin → Smart Product Tables → Add New
2. Layout Builder এ "Categories" column টি drag করে Active Layout এ নিয়ে আসুন
3. Settings এ hierarchical view enable করতে পারেন

### Available Columns:
- `image` - Product image
- `title` - Product title  
- `category` - Product categories
- `stock_status` - Stock availability (NEW!)
- `short_description` - Product short description (NEW!)
- `tags` - Product tags (NEW!)
- `rating` - Product ratings with stars (NEW!)
- `custom_field` - Custom meta field value (NEW!)
- `price` - Product price
- `sku` - Product SKU
- `add_to_cart` - Add to cart button

## Features

### Category Column:
- Category names clickable links হিসেবে দেখানো হয়
- Multiple categories comma দিয়ে আলাদা করা হয়

### Stock Status Column:
- In Stock, Out of Stock, On Backorder status দেখায়
- Stock quantity সহ দেখানো হয়
- Color coded status (green, red, orange)

### Short Description Column:
- Product short description দেখায়
- 100 characters এর বেশি হলে "..." দিয়ে কাটা হয়

### Tags Column:
- Product tags clickable links হিসেবে দেখানো হয়
- Multiple tags comma দিয়ে আলাদা

### Rating Column:
- Star rating (1-5 stars) দেখায়
- Average rating number সহ
- Review count দেখানোর option

### Custom Field Column:
- যে কোন custom meta field এর value দেখানো যায়
- Fallback value set করা যায়

### সাধারণ Features:
- Responsive design - mobile এ সুন্দরভাবে দেখায়
- Empty state handling সব কলামে

## CSS Classes
- `.smarttable-categories` - Category container
- `.smarttable-category-link` - Individual category links
- `.smarttable-category-name` - Category names (if not linked)
- `.smarttable-no-category` - "Uncategorized" text

## Testing
Test file: `test-category.php` - এই file টি কোন page/post এ include করে test করতে পারেন।