# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**WOKMASGO** is a CodeIgniter 4 web application running on XAMPP with a modular app-based architecture. It currently hosts two AI-powered applications:
- **Markdown Viewer**: Converts and previews Markdown files
- **Image Creator**: Generates images using Google Gemini 2.5 Flash Image (Nano Banana) via OpenRouter API

## Development Environment

### Running the Application

This project runs on XAMPP Apache server with the following configuration:

**Base URL**: `http://localhost/wokmasgo/`

The application uses a custom index.php in the root directory (not in public/) with URL rewriting configured via `.htaccess`.

Access the application:
```bash
# Ensure XAMPP Apache is running, then visit:
http://localhost/wokmasgo/                    # Landing page
http://localhost/wokmasgo/markdown-viewer     # Markdown Viewer app
http://localhost/wokmasgo/image-creator       # Image Creator app
```

### Dependencies

Install dependencies:
```bash
composer install
```

Update dependencies:
```bash
composer update
```

Run tests:
```bash
composer test
# or
./vendor/bin/phpunit
```

### Key Configuration

**Base URL** is set in `app/Config/App.php`:
```php
public string $baseURL = 'http://localhost/wokmasgo/';
```

Always use `base_url()` helper for generating URLs to assets, routes, and links.

## Architecture

### Template System

The application uses a **single unified template pattern** where all pages share the same layout structure:

**Main Template**: `app/Views/public_template.php`
- Contains navbar, footer, base styles, and page structure
- Accepts 4 optional variables (all have defaults):
  - `$page_title` - Browser tab title
  - `$main_content` - Page-specific HTML content
  - `$additional_css` - Page-specific CSS (raw CSS, no `<style>` tags)
  - `$additional_js` - Page-specific JavaScript (wrapped in `<script>` tags)

**Controller Pattern** (all controllers follow this):
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

### File Organization Per App

Each app follows this structure:
```
app/Controllers/AppName.php          # Controller with index() method
app/Views/app_name_content.php       # HTML content only
app/Views/app_name_styles.php        # CSS only (no <style> tags)
app/Views/app_name_scripts.php       # JavaScript only (no <script> tags)
```

### Routes Configuration

Routes are defined in `app/Config/Routes.php`:
```php
$routes->get('/', 'Landing::index');
$routes->get('app-name', 'AppName::index');
```

### Color Scheme

The application uses a consistent maroon and gold theme defined as CSS variables in `public_template.php`:

```css
--maroon: #800000          --gold: #FFD700
--maroon-dark: #5c0000     --gold-dark: #DAA520
--maroon-light: #a52a2a    --gold-light: #FFEC8B
--black: #000000           --white: #FFFFFF
--gray-light: #f8f9fa      --gray: #6c757d
```

Utility classes available:
- `.text-maroon`, `.text-gold`
- `.bg-maroon`, `.bg-gold`
- `.gradient-maroon-gold`, `.gradient-gold-maroon`, `.gradient-maroon-black`

## Adding New Apps

To add a new app to WOKMASGO:

1. **Create Controller** (`app/Controllers/AppName.php`):
   - Follow the controller pattern above
   - Extend `BaseController`

2. **Create Views**:
   - `app/Views/app_name_content.php` - HTML content
   - `app/Views/app_name_styles.php` - CSS (optional)
   - `app/Views/app_name_scripts.php` - JavaScript (optional)

3. **Add Route** (`app/Config/Routes.php`):
   ```php
   $routes->get('app-name', 'AppName::index');
   ```

4. **Add to Landing Page** (`app/Controllers/Landing.php`):
   Add app card to `getAvailableApps()` method:
   ```php
   [
       'id' => 3,
       'name' => 'App Name',
       'description' => 'Description of the app',
       'icon' => 'fas fa-icon-name',
       'url' => base_url('app-name'),
       'color' => 'maroon',
       'gradient' => 'gradient-maroon-gold'
   ]
   ```

## App-Specific Details

### Image Creator App

**API Integration**: Uses OpenRouter API with Google Gemini 2.5 Flash Image model

**Important**: The API key is currently hardcoded in `app/Views/image_creator_scripts.php:318`. When modifying:
```javascript
'Authorization': 'Bearer sk-or-v1-...'  // API key location
```

**API Request Structure**:
```javascript
{
    model: "google/gemini-2.5-flash-image",
    messages: [{ role: "user", content: "prompt" }],
    modalities: ["image", "text"],    // Required for image generation
    image_config: {
        aspect_ratio: "1:1"            // 10 options available
    }
}
```

**Response Format**: Returns base64-encoded PNG images in `data.choices[0].message.images[0].image_url.url`

**Supported Aspect Ratios**: 1:1, 2:3, 3:2, 3:4, 4:3, 4:5, 5:4, 9:16, 16:9, 21:9

### Markdown Viewer App

**Dependency**: Uses `erusev/parsedown` library for Markdown parsing

**Security**: Parsedown safe mode is enabled to prevent XSS attacks:
```php
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
```

**AJAX Endpoint**: `POST /markdown-viewer/render` returns JSON with rendered HTML

## Important Implementation Notes

### CSRF Protection
All forms must include CSRF tokens. In AJAX requests, update tokens after responses:
```javascript
const newToken = data.csrf_token;
document.querySelector('input[name="csrf_token_name"]').value = newToken;
```

### JavaScript in Views
When including JavaScript in `additional_js`, always wrap in `<script>` tags:
```php
$additionalJs = '<script>' . view('app_scripts') . '</script>';
```

CSS in `additional_css` should NOT include `<style>` tags.

### Asset References
Always use `base_url()` for asset paths:
```php
<img src="<?= base_url('assets/images/logo.png') ?>">
```

### Landing Page App Cards
The landing page dynamically displays all apps from `Landing::getAvailableApps()`. Each app card supports:
- Custom Font Awesome icon
- Color gradients
- Hover effects
- Direct routing to app URL

## Common Tasks

### Clearing Cache
```bash
php spark cache:clear
```

### Checking Routes
```bash
php spark routes
```

### Database Migrations (when needed)
```bash
php spark migrate
```

## Documentation References

- **Template System**: See `TEMPLATE_USAGE_GUIDE.md` for detailed template usage patterns
- **Configuration**: See `CONFIGURATION_CHECKLIST.md` for setup verification
- **Markdown Viewer**: See `MARKDOWN_VIEWER_DOCUMENTATION.md` for app-specific details
- **CodeIgniter 4**: https://codeigniter.com/user_guide/

## Key Design Decisions

1. **Single Template Pattern**: All pages use one template for consistency and maintainability
2. **Client-Side Processing**: Both apps use client-side JavaScript for rendering/generation to minimize server load
3. **AJAX-Based Workflows**: Forms submit via AJAX for better UX (no page reloads)
4. **Modular App Architecture**: Each app is self-contained with its own controller and views
5. **XAMPP Deployment**: Application runs from root directory with custom index.php instead of standard CodeIgniter public/ folder structure
