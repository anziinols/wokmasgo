# Markdown Viewer Application - Documentation

## Overview
The Markdown Viewer is a web-based application that allows users to convert Markdown text to HTML and preview the rendered output. Users can either upload a Markdown file or paste Markdown text directly into a textarea.

## Features

### ✨ Core Features
1. **Dual Input Methods:**
   - Upload `.md`, `.markdown`, or `.txt` files (max 5MB)
   - Paste Markdown text directly into a textarea

2. **Real-time Rendering:**
   - AJAX-based rendering (no page reload)
   - Instant preview of rendered HTML
   - Loading state during processing

3. **Copy to Clipboard:**
   - One-click copy of rendered HTML
   - Visual feedback on successful copy
   - Success message notification

4. **Security:**
   - CSRF protection on all forms
   - Safe mode enabled in Parsedown (prevents XSS)
   - File type validation
   - File size limits (5MB max)

5. **User Experience:**
   - Responsive design (mobile, tablet, desktop)
   - WOKMASGO color scheme (maroon and gold)
   - Clear error and success messages
   - Quick Markdown syntax guide
   - Tab-based interface for input methods

## Technical Implementation

### Files Created

#### 1. Controller
**File:** `app/Controllers/MarkdownViewer.php`

**Methods:**
- `index()` - Display the Markdown Viewer page
- `render()` - Process and render Markdown via AJAX

**Key Features:**
- Uses Parsedown library for Markdown parsing
- Validates file uploads (type, size, validity)
- Returns JSON responses for AJAX requests
- Enables safe mode to prevent XSS attacks
- Comprehensive error handling

#### 2. Views

**Content View:** `app/Views/markdown_viewer_content.php`
- Hero section with title and description
- Tab-based input interface (Text/File)
- Text input form with textarea
- File upload form with file info display
- Preview section with loading/empty/content states
- Copy button for rendered HTML
- Alert messages for success/error
- Quick Markdown syntax guide

**Styles:** `app/Views/markdown_viewer_styles.php`
- Custom CSS for Markdown Viewer
- Hero section styling
- Input card and preview card layouts
- Tab navigation styles
- Loading, empty, and preview states
- Markdown output formatting (headings, code, tables, etc.)
- Responsive design breakpoints
- WOKMASGO color scheme integration

**Scripts:** `app/Views/markdown_viewer_scripts.php`
- Form submission handlers (text and file)
- AJAX request to render endpoint
- Copy to clipboard functionality
- State management (loading, empty, preview)
- Alert message handling
- File size formatting
- CSRF token updates after AJAX

#### 3. Routes
**File:** `app/Config/Routes.php`

**Routes Added:**
```php
$routes->get('markdown-viewer', 'MarkdownViewer::index');
$routes->post('markdown-viewer/render', 'MarkdownViewer::render');
```

#### 4. Landing Page Update
**File:** `app/Controllers/Landing.php`

**Added App Card:**
- Name: "Markdown Viewer"
- Description: "Convert and preview Markdown files"
- Icon: Font Awesome Markdown icon
- URL: `base_url('markdown-viewer')`
- Gradient: Maroon to Gold

### Dependencies

**Parsedown Library:**
- Package: `erusev/parsedown`
- Version: `^1.7`
- Installed via Composer
- Used for Markdown to HTML conversion
- Safe mode enabled for security

## Usage Instructions

### Accessing the Application
Navigate to:
```
http://localhost/wokmasgo/markdown-viewer
```

### Using Text Input
1. Click on the "Paste Text" tab
2. Enter or paste your Markdown text in the textarea
3. Click "Render Preview" button
4. View the rendered HTML in the preview section
5. Click "Copy HTML" to copy the rendered output

### Using File Upload
1. Click on the "Upload File" tab
2. Click "Choose File" and select a `.md`, `.markdown`, or `.txt` file
3. File information will be displayed (name and size)
4. Click "Render Preview" button
5. View the rendered HTML in the preview section
6. Click "Copy HTML" to copy the rendered output

### Supported Markdown Syntax

The application supports standard Markdown syntax:

| Syntax | Description |
|--------|-------------|
| `# Heading 1` | Main heading |
| `## Heading 2` | Subheading |
| `**bold**` | Bold text |
| `*italic*` | Italic text |
| `[Link](url)` | Hyperlink |
| `- Item` | Unordered list |
| `1. Item` | Ordered list |
| `` `code` `` | Inline code |
| ` ```code``` ` | Code block |
| `> Quote` | Blockquote |
| `![Alt](image.jpg)` | Image |
| Tables | Markdown tables |

## Security Features

### 1. CSRF Protection
- All forms include CSRF tokens
- Tokens are validated on submission
- Tokens are refreshed after AJAX requests

### 2. File Upload Security
- File type validation (only .md, .markdown, .txt)
- File size limit (5MB maximum)
- File validity checks
- Temporary file handling

### 3. XSS Prevention
- Parsedown safe mode enabled
- Prevents execution of malicious scripts
- Sanitizes HTML output

### 4. Input Validation
- Server-side validation for all inputs
- Empty input checks
- File existence checks
- Error handling for invalid data

## Error Handling

### Client-Side Errors
- Empty text input
- No file selected
- Invalid file type
- File size exceeded

### Server-Side Errors
- Invalid request method
- File upload errors
- File reading errors
- Markdown parsing errors

### Error Messages
All errors are displayed in a dismissible alert with:
- Clear error description
- Icon indicator
- Auto-dismiss after 5 seconds
- Manual dismiss option

## Responsive Design

### Desktop (>1024px)
- Two-column layout (input and preview side-by-side)
- Full-width preview area
- Large hero section

### Tablet (768-1024px)
- Two-column layout maintained
- Adjusted padding and spacing
- Responsive preview height

### Mobile (<768px)
- Single-column layout
- Stacked input and preview sections
- Optimized button sizes
- Collapsible navbar

## Color Scheme

The application uses the WOKMASGO color palette:

| Color | Hex Code | Usage |
|-------|----------|-------|
| Maroon | #800000 | Primary color, headings, buttons |
| Maroon Dark | #5c0000 | Gradients, hover states |
| Gold | #FFD700 | Accents, highlights, copy button |
| Gold Dark | #DAA520 | Gradients |
| White | #FFFFFF | Background, text on dark |
| Gray Light | #f8f9fa | Preview background |

## API Endpoints

### GET /markdown-viewer
**Description:** Display the Markdown Viewer page

**Response:** HTML page with the Markdown Viewer interface

### POST /markdown-viewer/render
**Description:** Render Markdown to HTML via AJAX

**Request Parameters:**
- `source` (string): 'text' or 'file'
- `markdown_text` (string): Markdown text (if source is 'text')
- `markdown_file` (file): Markdown file (if source is 'file')
- CSRF token

**Response (Success):**
```json
{
    "success": true,
    "html": "<rendered HTML>",
    "message": "Markdown rendered successfully"
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Error description"
}
```

## Testing Checklist

### Functionality Tests
- [ ] Text input renders correctly
- [ ] File upload works with .md files
- [ ] File upload works with .markdown files
- [ ] File upload works with .txt files
- [ ] File size validation (reject >5MB)
- [ ] File type validation (reject other types)
- [ ] Copy to clipboard works
- [ ] Success messages display
- [ ] Error messages display
- [ ] CSRF protection works
- [ ] Tab switching works

### Markdown Syntax Tests
- [ ] Headings render correctly (H1-H6)
- [ ] Bold text renders
- [ ] Italic text renders
- [ ] Links render and are clickable
- [ ] Lists render (ordered and unordered)
- [ ] Code blocks render with styling
- [ ] Inline code renders
- [ ] Blockquotes render
- [ ] Tables render correctly

### Responsive Design Tests
- [ ] Desktop layout (>1024px)
- [ ] Tablet layout (768-1024px)
- [ ] Mobile layout (<768px)
- [ ] Navbar collapses on mobile
- [ ] Buttons are accessible on mobile

### Security Tests
- [ ] XSS prevention (try malicious scripts)
- [ ] CSRF token validation
- [ ] File type validation
- [ ] File size validation

## Troubleshooting

### Issue: "No file uploaded" error
**Solution:** Ensure a file is selected before clicking "Render Preview"

### Issue: "Invalid file type" error
**Solution:** Only .md, .markdown, and .txt files are accepted

### Issue: "File size exceeds 5MB limit" error
**Solution:** Reduce file size or split into smaller files

### Issue: Copy to clipboard doesn't work
**Solution:** Ensure browser supports clipboard API. Try using a modern browser.

### Issue: Preview doesn't show
**Solution:** Check browser console for errors. Ensure JavaScript is enabled.

## Future Enhancements

Potential features for future versions:
- [ ] Download rendered HTML as file
- [ ] Syntax highlighting for code blocks
- [ ] Dark mode toggle
- [ ] Markdown editor with syntax highlighting
- [ ] Live preview (as you type)
- [ ] Export to PDF
- [ ] Save/load from database
- [ ] Share rendered output via link
- [ ] Multiple file upload
- [ ] Drag and drop file upload

## Maintenance

### Updating Parsedown
To update the Parsedown library:
```bash
composer update erusev/parsedown
```

### Adding New Markdown Features
Edit `app/Controllers/MarkdownViewer.php` and configure Parsedown options:
```php
$parsedown = new Parsedown();
$parsedown->setSafeMode(true);
// Add more configuration here
```

## Support

For issues or questions:
1. Check this documentation
2. Review the code comments
3. Check browser console for errors
4. Verify XAMPP Apache is running
5. Ensure base URL is correct in `app/Config/App.php`

---

**Version:** 1.0  
**Last Updated:** <?= date('Y-m-d') ?>  
**Author:** WOKMASGO Development Team

