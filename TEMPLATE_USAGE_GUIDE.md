# WOKMASGO Single Template System - Usage Guide

## Overview
The application now uses a single template file (`public_template.php`) instead of multiple template pieces. This makes it easier to maintain and understand the structure.

## Base URL Configuration
The application is configured with the following base URL:
```
http://localhost/wokmasgo/
```

This is set in `app/Config/App.php` and ensures all `base_url()` helper functions generate correct URLs for assets, links, and routes.

## File Structure

```
app/Views/
├── public_template.php       # Main template file (header, footer, navbar, base styles)
├── landing_content.php        # Landing page content only
├── landing_styles.php         # Landing page specific CSS
└── landing_scripts.php        # Landing page specific JavaScript
```

## How It Works

### 1. Main Template (`public_template.php`)
This file contains:
- Complete HTML structure (DOCTYPE, head, body)
- Navbar with logo and navigation links
- Footer with logo, links, and social media icons
- Base CSS styles (color scheme, navbar, footer, utilities)
- Bootstrap 5 and Font Awesome CDN links
- Placeholders for dynamic content:
  - `$page_title` - Page title (optional, defaults to 'WOKMASGO')
  - `$main_content` - Main page content (optional, defaults to empty string)
  - `$additional_css` - Page-specific CSS (optional, defaults to empty string)
  - `$additional_js` - Page-specific JavaScript (optional, defaults to empty string)

**Important:** All variables use the null coalescing operator (`??`) so they are optional. If not provided, the template will use default values.

### 2. Content Files
Content files contain only the HTML content for that specific page (no header, footer, or complete HTML structure).

### 3. Style Files
Style files contain only CSS rules (no `<style>` tags).

### 4. Script Files
Script files contain only JavaScript code (no `<script>` tags).

## How to Use This Template System

### Example: Landing Page Controller

```php
<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index(): string
    {
        // 1. Prepare data for the content view
        $contentData = [
            'apps' => $this->getAvailableApps()
        ];
        
        // 2. Load the content view
        $mainContent = view('landing_content', $contentData);
        
        // 3. Load additional CSS and JS (optional)
        $additionalCss = view('landing_styles');
        $additionalJs = '<script>' . view('landing_scripts') . '</script>';
        
        // 4. Prepare data for the template
        $templateData = [
            'page_title' => 'WOKMASGO - Home',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];
        
        // 5. Return the complete page
        return view('public_template', $templateData);
    }
}
```

## Creating a New Page

### Step 1: Create Content View
Create `app/Views/your_page_content.php`:

```php
<div class="container">
    <h1>Your Page Title</h1>
    <p>Your content here...</p>
</div>
```

### Step 2: Create Styles (Optional)
Create `app/Views/your_page_styles.php`:

```css
.your-custom-class {
    color: var(--maroon);
    font-size: 1.2rem;
}
```

### Step 3: Create Scripts (Optional)
Create `app/Views/your_page_scripts.php`:

```javascript
console.log('Your page loaded');
document.querySelector('.your-button').addEventListener('click', function() {
    alert('Button clicked!');
});
```

### Step 4: Create Controller
Create `app/Controllers/YourPage.php`:

```php
<?php

namespace App\Controllers;

class YourPage extends BaseController
{
    public function index(): string
    {
        // Load content
        $mainContent = view('your_page_content');
        
        // Load styles and scripts (optional)
        $additionalCss = view('your_page_styles');
        $additionalJs = '<script>' . view('your_page_scripts') . '</script>';
        
        // Prepare template data
        $templateData = [
            'page_title' => 'Your Page Title',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];
        
        // Return complete page
        return view('public_template', $templateData);
    }
}
```

### Step 5: Add Route
In `app/Config/Routes.php`:

```php
$routes->get('your-page', 'YourPage::index');
```

## Simple Example (Without Additional CSS/JS)

If you don't need custom styles or scripts:

```php
public function index(): string
{
    $mainContent = view('your_page_content', ['data' => 'value']);
    
    $templateData = [
        'page_title' => 'Your Page',
        'main_content' => $mainContent
    ];
    
    return view('public_template', $templateData);
}
```

## Available CSS Variables

The template includes these CSS variables you can use:

```css
--maroon: #800000
--maroon-dark: #5c0000
--maroon-light: #a52a2a
--gold: #FFD700
--gold-dark: #DAA520
--gold-light: #FFEC8B
--black: #000000
--white: #FFFFFF
--gray-light: #f8f9fa
--gray: #6c757d
```

## Available Utility Classes

```css
.text-maroon       /* Maroon text color */
.text-gold         /* Gold text color */
.bg-maroon         /* Maroon background */
.bg-gold           /* Gold background */
.gradient-maroon-gold    /* Maroon to gold gradient */
.gradient-gold-maroon    /* Gold to maroon gradient */
.gradient-maroon-black   /* Maroon to black gradient */
```

## Template Variables Reference

### Required Variables
**None!** All variables are optional with default values.

### Optional Variables

| Variable | Type | Default | Description | Example |
|----------|------|---------|-------------|---------|
| `$page_title` | string | `'WOKMASGO'` | Page title shown in browser tab | `'Dashboard - WOKMASGO'` |
| `$main_content` | string | `''` | Main page content (HTML) | `view('dashboard_content', $data)` |
| `$additional_css` | string | `''` | Page-specific CSS styles | `view('dashboard_styles')` |
| `$additional_js` | string | `''` | Page-specific JavaScript | `'<script>' . view('dashboard_scripts') . '</script>'` |

### Usage Pattern

```php
// Minimal usage (only content)
$templateData = [
    'main_content' => view('my_content')
];

// Full usage (all variables)
$templateData = [
    'page_title' => 'My Page - WOKMASGO',
    'main_content' => view('my_content', $data),
    'additional_css' => view('my_styles'),
    'additional_js' => '<script>' . view('my_scripts') . '</script>'
];

return view('public_template', $templateData);
```

## Tips and Best Practices

1. **Keep content files clean** - Only HTML content, no complete page structure
2. **Use CSS variables** - For consistent theming across pages
3. **Reuse the template** - All pages should use `public_template.php`
4. **Optional CSS/JS** - Only include if your page needs custom styling or scripts
5. **Pass data properly** - Use the `$contentData` array to pass data to content views
6. **Consistent naming** - Name your files descriptively (e.g., `dashboard_content.php`, `dashboard_styles.php`)
7. **Use base_url()** - Always use `base_url()` helper for links and assets to ensure correct URLs

## Customizing the Template

To modify the navbar, footer, or base styles, edit `app/Views/public_template.php`.

Changes will automatically apply to all pages using the template.

## Benefits of This Approach

✅ **Single source of truth** - One template file for all pages
✅ **Easy maintenance** - Update header/footer in one place
✅ **Clean separation** - Content, styles, and scripts are separate
✅ **Flexible** - Easy to add page-specific CSS/JS when needed
✅ **Simple** - Straightforward to understand and use
✅ **Consistent** - All pages have the same structure and theme

## Troubleshooting

### Assets Not Loading (404 Errors)

**Problem:** Images, CSS, or JS files return 404 errors.

**Solution:**
1. Verify base URL is correct in `app/Config/App.php`:
   ```php
   public string $baseURL = 'http://localhost/wokmasgo/';
   ```
2. Ensure `.htaccess` file exists in the root directory
3. Check that assets are in the `public/assets/` folder
4. Always use `base_url()` helper:
   ```php
   <img src="<?= base_url('assets/images/logo.png') ?>">
   ```

### Page Shows Blank or Variables Not Displaying

**Problem:** Page is blank or variables show as `<?= $variable ?>`

**Solution:**
1. Ensure you're passing data to the template:
   ```php
   return view('public_template', $templateData);
   ```
2. Check that PHP short tags are enabled (they should be by default in CI4)
3. Verify variable names match exactly (case-sensitive)

### CSS/JS Not Applied

**Problem:** Custom styles or scripts don't work.

**Solution:**
1. Ensure you're wrapping scripts in `<script>` tags:
   ```php
   $additionalJs = '<script>' . view('my_scripts') . '</script>';
   ```
2. Check that style/script files don't include `<style>` or `<script>` tags
3. Verify the files are being loaded in the controller

### Routes Not Working

**Problem:** URLs return 404 errors.

**Solution:**
1. Check routes are defined in `app/Config/Routes.php`
2. Verify controller and method names match the route
3. Clear route cache if needed:
   ```bash
   php spark cache:clear
   ```
4. Ensure `.htaccess` is properly configured for URL rewriting

## Quick Reference

### Access Your Application
```
http://localhost/wokmasgo/
```

### Common URLs
- Home: `http://localhost/wokmasgo/`
- Dashboard: `http://localhost/wokmasgo/dashboard`
- Users: `http://localhost/wokmasgo/users`

### File Locations
- Template: `app/Views/public_template.php`
- Controllers: `app/Controllers/`
- Routes: `app/Config/Routes.php`
- Base URL Config: `app/Config/App.php`

### Helper Functions
```php
base_url()              // Returns: http://localhost/wokmasgo/
base_url('dashboard')   // Returns: http://localhost/wokmasgo/dashboard
base_url('assets/images/logo.png')  // Returns: http://localhost/wokmasgo/assets/images/logo.png
```

