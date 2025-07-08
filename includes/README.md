# Shared Navbar System

This folder contains shared components to ensure consistency across the entire website.

## How to Use the Navbar

1. Include the following in your page's `<head>` section:
   ```html
   <link rel="stylesheet" href="../includes/navbar_styles.css">
   ```

2. At the beginning of your PHP file (after the opening `<body>` tag), add:
   ```php
   <?php
   // Include the navbar function
   require_once('../includes/navbar.php');
   ?>
   ```

3. In your main content area, call the navbar function with the appropriate active page:
   ```php
   <div class="main">
       <?php 
       // Generate the navbar with the active page
       generate_navbar('page_id'); 
       ?>
       
       <!-- Your page content here -->
   </div>
   ```

## Available Page IDs

Use one of these IDs to set the active page in the navbar:
- `'home'` - For the front page
- `'about'` - For the about page
- `'brands'` - For the brands page
- `'cars'` - For the cars page
- `'cart'` - For the cart page

## Features

- **Consistent Navbar**: Ensures the same navbar appears on all pages
- **Active Page Highlighting**: Automatically highlights the current page in the navigation
- **Login/Logout System**: Handles displaying login/register buttons or user welcome + logout
- **Message System**: Displays success/error/info messages consistently across all pages
- **Centralized Styling**: All navbar styles are defined in one place for easy updates
- **Dynamic Background**: Navbar background automatically changes to white when scrolling down

## Files Included

- **navbar.php**: Contains the main function to generate the navbar
- **navbar_styles.css**: Contains all styles for the navbar
- **navbar_script.js**: Handles the dynamic background color change on scroll

## Example

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="../includes/navbar_styles.css">
    <link rel="stylesheet" href="your_page_style.css">
</head>
<body>
    <?php
    // Include the navbar function
    require_once('../includes/navbar.php');
    ?>
    <div class="main">
        <?php 
        // Generate the navbar with 'about' as the active page
        generate_navbar('about'); 
        ?>
        
        <!-- Your page content here -->
    </div>
</body>
</html>
```

Note: The JavaScript for navbar behavior is automatically included by the `generate_navbar()` function, so you don't need to add it separately. 