# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**WOKMASGO** is a CodeIgniter 4 web application running on XAMPP with a modular app-based architecture. It currently hosts two AI-powered applications:
- **Markdown Viewer**: Client-side Markdown conversion and preview using Parsedown
- **Image Creator**: AI-powered logo and flyer generation using Google Gemini 2.5 Flash Image via OpenRouter API

## Development Commands

### Running the Application

This project runs on XAMPP Apache server:

```bash
# Start XAMPP Apache (if not already running)
# Access the application at: http://localhost/wokmasgo/

# Main routes:
# http://localhost/wokmasgo/                    # Landing page
# http://localhost/wokmasgo/markdown-viewer     # Markdown Viewer
# http://localhost/wokmasgo/image-creator       # Image Creator
```

### Dependency Management

```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Install specific package
composer require package/name
```

### Testing

```bash
# Run all tests
composer test
# or
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/unit/SomeTest.php

# Run tests with coverage
./vendor/bin/phpunit --coverage-html build/logs/html
```

### CLI Commands (Spark)

```bash
# List all available routes
php spark routes

# Clear cache
php spark cache:clear

# Run database migrations (when needed)
php spark migrate

# Rollback migrations
php spark migrate:rollback

# List available commands
php spark list
```

### Debugging

```bash
# Enable debug mode in .env
CI_ENVIRONMENT = development

# View logs
tail -f writable/logs/log-*.php

# Check Apache error logs (XAMPP)
# Windows: C:\xampp\apache\logs\error.log
# Linux: /opt/lampp/logs/error_log
```

## Architecture

### Directory Structure

```
wokmasgo/
├── app/
│   ├── Config/
│   │   ├── App.php          # Base URL and app settings
│   │   ├── Routes.php       # Route definitions
│   │   └── Filters.php      # CSRF and security filters
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── Landing.php      # Landing page with app cards
│   │   ├── ImageCreator.php # Image generation app
│   │   └── MarkdownViewer.php
│   └── Views/
│       ├── public_template.php           # Main template (all pages use this)
│       ├── {app_name}_content.php       # HTML content for each app
│       ├── {app_name}_styles.php        # CSS for each app
│       └── {app_name}_scripts.php       # JavaScript for each app
├── public/
│   ├── assets/
│   │   └── images/
│   └── index.php            # Standard CI4 entry point (not used in XAMPP setup)
├── index.php                # Custom entry point in root (XAMPP setup)
├── .env                     # Environment config (API keys, base URL)
└── composer.json
```

### Template System

All pages use a **single unified template** (`app/Views/public_template.php`) with consistent navbar, footer, and styling.

**Template Variables** (all optional with defaults):
- `$page_title` - Browser tab title
- `$main_content` - Page-specific HTML content
- `$additional_css` - Page-specific CSS (raw CSS, no `<style>` tags)
- `$additional_js` - Page-specific JavaScript (wrapped in `<script>` tags)

**Controller Pattern** (standard for all apps):

```php
public function index(): string
{
    // 1. Load page content
    $mainContent = view('app_name_content', $data);

    // 2. Load additional CSS/JS (optional)
    $additionalCss = view('app_name_styles');
    $additionalJs = '<script>' . view('app_name_scripts') . '</script>';

    // 3. Assemble template data
    $templateData = [
        'page_title' => 'App Name - WOKMASGO',
        'main_content' => $mainContent,
        'additional_css' => $additionalCss,
        'additional_js' => $additionalJs
    ];

    // 4. Return complete page
    return view('public_template', $templateData);
}
```

### Configuration

**Base URL**: Set in `app/Config/App.php` (line 19):
```php
public string $baseURL = 'http://localhost/wokmasgo/';
```

**CRITICAL**: Always use `base_url()` helper for generating URLs:
```php
// Correct
<img src="<?= base_url('assets/images/logo.png') ?>">
<a href="<?= base_url('image-creator') ?>">

// Incorrect (will break in production)
<img src="/assets/images/logo.png">
```

### Routing

Routes are defined in `app/Config/Routes.php`:

```php
// Basic route
$routes->get('app-name', 'AppName::index');

// Route with parameter
$routes->get('app-name/(:num)', 'AppName::view/$1');

// POST route
$routes->post('app-name/process', 'AppName::process');
```

### Color Scheme and Design System

Maroon and gold theme with CSS variables in `public_template.php`:

```css
--maroon: #800000          --gold: #FFD700
--maroon-dark: #5c0000     --gold-dark: #DAA520
--maroon-light: #a52a2a    --gold-light: #FFEC8B
```

**Utility Classes**:
- Text: `.text-maroon`, `.text-gold`
- Backgrounds: `.bg-maroon`, `.bg-gold`
- Gradients: `.gradient-maroon-gold`, `.gradient-gold-maroon`, `.gradient-maroon-black`

## App-Specific Implementation Details

### Image Creator App

**Purpose**: AI-powered logo and flyer generation with template editing

**Key Features**:
- Logo creation from text prompts
- Flyer creation with product image integration
- Image editing with base image + modifications

**API Integration**:
- Provider: OpenRouter API
- Model: `google/gemini-2.5-flash-image` (Nano Banana)
- API key stored in `.env` as `openrouter.apiKey`
- Backend endpoint: `ImageCreator::generate()` (POST `/image-creator/generate`)

**API Request Structure** (backend only):
```javascript
{
    model: "google/gemini-2.5-flash-image",
    messages: [{ role: "user", content: "prompt" }],
    modalities: ["image", "text"],    // Required for image generation
    image_config: {
        aspect_ratio: "1:1"            // Options: 1:1, 2:3, 3:2, 3:4, 4:3, 4:5, 5:4, 9:16, 16:9, 21:9
    }
}
```

**Response Format**: Base64-encoded PNG in `data.choices[0].message.images[0].image_url.url`

**Mobile Compatibility Architecture**:

The Image Creator app uses a **dual-storage pattern** to handle mobile Chrome file reference issues:

```javascript
// State variables store BOTH File objects and data URLs
let templateFile = null;
let templateDataUrl = null;              // Stored data URL prevents stale File references
let productPreviews = [];                // Array of {file, dataUrl} objects
let baseImagePreviews = [];              // Array of {file, dataUrl} objects
```

**Key Mobile Fixes** (implemented in recent commits):
1. **Storage Clearing on Page Load**: Prevents stale file references from previous sessions
2. **Data URL Caching**: Files are read immediately on upload and stored as data URLs
3. **HEIC/HEIF Conversion**: Automatic conversion to JPEG using `heic2any` library (loaded in `public_template.php`)
4. **Touch Event Handlers**: Dedicated touchend events for mobile card selection
5. **Multiple Fallback Strategies**: Object URL → FileReader → Direct read with error handling

**File Processing Pattern**:
```javascript
// 1. Read file immediately on upload (prevents mobile permission errors)
function createPreviewUrl(file) {
    return new Promise(function(resolve, reject) {
        // Check for HEIC/HEIF and convert if needed
        if (isHeicFile(file)) {
            heic2any({
                blob: file,
                toType: 'image/jpeg',
                quality: 0.8
            }).then(function(convertedBlob) {
                // Read converted blob as data URL
                var reader = new FileReader();
                reader.onload = function(e) {
                    var convertedFile = new File([convertedBlob],
                        file.name.replace(/\.heic$/i, '.jpg'),
                        { type: 'image/jpeg' });
                    resolve({ dataUrl: e.target.result, file: convertedFile });
                };
                reader.readAsDataURL(convertedBlob);
            });
        } else {
            // Standard image - read directly
            var reader = new FileReader();
            reader.onload = function(e) {
                resolve({ dataUrl: e.target.result, file: file });
            };
            reader.readAsDataURL(file);
        }
    });
}

// 2. Store both file and data URL
templateDataUrl = result.dataUrl;
templateFile = result.file;

// 3. Use stored data URL for API calls (not File object)
contentParts.push({
    type: "image_url",
    image_url: { url: templateDataUrl }  // Uses cached data URL
});
```

**Supported Image Formats**: JPG, PNG, GIF, WebP, AVIF, HEIC, HEIF (HEIC/HEIF auto-converted to JPEG)

### Markdown Viewer App

**Purpose**: Client-side Markdown conversion and preview

**Dependencies**:
- PHP: `erusev/parsedown` (composer package)
- JavaScript: Client-side rendering

**Security**: Parsedown safe mode enabled to prevent XSS:
```php
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
```

## External Dependencies

### Frontend Libraries (CDN)

Loaded globally in `public_template.php`:
- **Bootstrap 5.3.0**: UI framework
- **Font Awesome 6.4.0**: Icons
- **Google Fonts (Poppins)**: Typography
- **heic2any 0.0.4**: HEIC/HEIF to JPEG conversion for iPhone photos (critical for Image Creator mobile support)

### PHP Dependencies (Composer)

```json
{
    "erusev/parsedown": "^1.7"  // Markdown parsing (Markdown Viewer)
}
```

## Adding New Apps to WOKMASGO

Follow these steps to add a new application:

### 1. Create Controller

File: `app/Controllers/AppName.php`

```php
<?php
namespace App\Controllers;

class AppName extends BaseController
{
    public function index(): string
    {
        $mainContent = view('app_name_content');
        $additionalCss = view('app_name_styles');
        $additionalJs = '<script>' . view('app_name_scripts') . '</script>';

        $templateData = [
            'page_title' => 'App Name - WOKMASGO',
            'main_content' => $mainContent,
            'additional_css' => $additionalCss,
            'additional_js' => $additionalJs
        ];

        return view('public_template', $templateData);
    }
}
```

### 2. Create Views

Create three view files in `app/Views/`:

- `app_name_content.php` - HTML structure only
- `app_name_styles.php` - CSS only (no `<style>` tags)
- `app_name_scripts.php` - JavaScript only (no `<script>` tags)

### 3. Add Route

File: `app/Config/Routes.php`

```php
$routes->get('app-name', 'AppName::index');
```

### 4. Add to Landing Page

File: `app/Controllers/Landing.php`

Add to `getAvailableApps()` method:

```php
[
    'id' => 3,
    'name' => 'App Name',
    'description' => 'Brief description of what the app does',
    'icon' => 'fas fa-icon-name',  // Font Awesome icon
    'url' => base_url('app-name'),
    'color' => 'maroon',           // or 'gold'
    'gradient' => 'gradient-maroon-gold'
]
```

### 5. Test the App

```bash
# Check route is registered
php spark routes

# Access in browser
# http://localhost/wokmasgo/app-name
```

## Security and Best Practices

### CSRF Protection

All forms must include CSRF tokens:

```php
// In view
<form method="post">
    <?= csrf_field() ?>
    <!-- form fields -->
</form>
```

For AJAX requests, update tokens after responses:

```javascript
fetch(url, {
    method: 'POST',
    body: formData,
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    }
})
.then(response => response.json())
.then(data => {
    // Update CSRF token for next request
    const newToken = data.csrf_token;
    document.querySelector('input[name="csrf_token_name"]').value = newToken;
});
```

### API Key Management

- Store API keys in `.env` file (never commit to git)
- `.env` is in `.gitignore`
- Access via `getenv()` or Config classes
- Never expose API keys to frontend JavaScript

```php
// In controller
$apiKey = getenv('openrouter.apiKey');
```

### File Upload Security

- Validate file types on both client and server
- Use `is_uploaded_file()` to verify uploads
- Sanitize file names
- Store uploads outside of `public/` directory when possible

### Mobile Compatibility

**Critical Pattern**: Always use the **dual-storage approach** for file uploads:

```javascript
// ❌ WRONG - Direct File object (stale on mobile Chrome)
fetch(apiUrl, {
    body: JSON.stringify({ image: fileObject })
});

// ✅ CORRECT - Pre-read data URL stored on upload
function createPreviewUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve({
            file: file,
            dataUrl: e.target.result  // Store immediately
        });
        reader.readAsDataURL(file);
    });
}

// Use stored data URL for API calls
fetch(apiUrl, {
    body: JSON.stringify({ image: storedDataUrl })
});
```

**Implementation Checklist**:
- ✅ Read files immediately in event handlers (prevents mobile permission errors)
- ✅ Store both File objects AND data URLs
- ✅ Use data URLs (not File objects) for API requests
- ✅ Clear localStorage/sessionStorage on page load
- ✅ Include HEIC/HEIF support with `heic2any` library
- ✅ Add touch event handlers (`touchend`) for mobile card selection
- ✅ Test on actual mobile devices (Chrome, Safari)
- ✅ Use ES5-compatible JavaScript for broader mobile browser support

## Important Implementation Notes

### Storage Clearing Pattern for Mobile

Clear app-specific browser storage on page load to prevent stale file references:

```javascript
// At the top of app scripts (executed immediately)
(function() {
    try {
        // Clear localStorage
        const keysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('appPrefix')) {
                keysToRemove.push(key);
            }
        }
        keysToRemove.forEach(key => localStorage.removeItem(key));

        // Clear sessionStorage similarly
    } catch (e) {
        console.log('Error clearing storage:', e);
    }
})();
```

This prevents mobile browsers from attempting to use stale File object references from previous sessions.

### JavaScript in Views

When including JavaScript in `additional_js`, always wrap in `<script>` tags:

```php
// Correct
$additionalJs = '<script>' . view('app_scripts') . '</script>';

// Incorrect
$additionalJs = view('app_scripts');
```

CSS in `additional_css` should NOT include `<style>` tags.

### XAMPP Deployment

This application uses a non-standard CodeIgniter setup:
- Entry point is `index.php` in root directory (not `public/index.php`)
- `.htaccess` in root handles URL rewriting
- Base URL includes `/wokmasgo/` subdirectory

When deploying to production, consider moving to standard CI4 structure with `public/` as web root.

### Debugging Tips

1. **Enable Debug Toolbar**: Set in `.env`:
   ```
   # app.toolbarEnabled = true
   ```
   (Currently disabled to prevent JavaScript conflicts)

2. **Check Logs**: Located in `writable/logs/log-*.php`

3. **AJAX Debugging**: Check browser console and Network tab

4. **Mobile Debugging**:
   - Chrome DevTools Remote Debugging for Android
   - Safari Web Inspector for iOS
   - Add console.log statements in mobile-specific code

## Documentation References

- **CodeIgniter 4 User Guide**: https://codeigniter.com/user_guide/
- **Template Usage Guide**: `dev_guide/TEMPLATE_USAGE_GUIDE.md`
- **Configuration Checklist**: `dev_guide/CONFIGURATION_CHECKLIST.md`
- **Markdown Viewer Docs**: `dev_guide/MARKDOWN_VIEWER_DOCUMENTATION.md`
- **OpenRouter API**: https://openrouter.ai/docs
- **Google Gemini**: https://ai.google.dev/

## Key Design Decisions

1. **Single Template Pattern**: All pages use one template (`public_template.php`) for consistency
2. **Client-Side Processing**: Apps use client-side JavaScript to minimize server load
3. **AJAX-Based Workflows**: Forms submit via AJAX for better UX (no page reloads)
4. **Modular App Architecture**: Each app is self-contained with its own controller and views
5. **XAMPP Deployment**: Root directory deployment for XAMPP compatibility
6. **Dual-Storage Pattern for Mobile**: Files stored as BOTH File objects and data URLs to prevent mobile Chrome stale reference errors
7. **Immediate File Reading**: Files converted to data URLs in event handlers to avoid mobile permission issues
8. **ES5 JavaScript Compatibility**: Uses ES5 syntax (no arrow functions, optional chaining) for broader mobile browser support
9. **Security-First**: CSRF protection, safe mode parsing, backend API key handling
