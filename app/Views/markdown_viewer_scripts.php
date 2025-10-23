// Load marked.js and DOMPurify from CDN
const markedScript = document.createElement('script');
markedScript.src = 'https://cdn.jsdelivr.net/npm/marked/marked.min.js';
document.head.appendChild(markedScript);

const purifyScript = document.createElement('script');
purifyScript.src = 'https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js';
document.head.appendChild(purifyScript);

// Wait for libraries to load
Promise.all([
    new Promise(resolve => markedScript.onload = resolve),
    new Promise(resolve => purifyScript.onload = resolve)
]).then(() => {
    initMarkdownViewer();
});

// Markdown Viewer JavaScript
function initMarkdownViewer() {
    // Elements
    const textForm = document.getElementById('textForm');
    const fileForm = document.getElementById('fileForm');
    const markdownFile = document.getElementById('markdownFile');
    const markdownText = document.getElementById('markdownText');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const copyBtn = document.getElementById('copyBtn');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const previewContent = document.getElementById('previewContent');
    const markdownOutput = document.getElementById('markdownOutput');
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');

    // File input change handler
    markdownFile.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];

            // Validate file type
            const validExtensions = ['md', 'markdown', 'txt'];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!validExtensions.includes(fileExtension)) {
                showError('Invalid file type. Only .md, .markdown, and .txt files are allowed.');
                this.value = '';
                fileInfo.style.display = 'none';
                return;
            }

            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                showError('File size exceeds 5MB limit');
                this.value = '';
                fileInfo.style.display = 'none';
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.style.display = 'block';
        } else {
            fileInfo.style.display = 'none';
        }
    });

    // Text form submit handler
    textForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const text = markdownText.value.trim();

        if (!text) {
            showError('Please enter some Markdown text');
            return;
        }

        renderMarkdown(text);
    });

    // File form submit handler
    fileForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const file = markdownFile.files[0];

        if (!file) {
            showError('Please select a file to upload');
            return;
        }

        // Read file content using FileReader API
        const reader = new FileReader();

        reader.onload = function(event) {
            const text = event.target.result;
            renderMarkdown(text);
        };

        reader.onerror = function() {
            showError('Failed to read file. Please try again.');
            showEmpty();
        };

        reader.readAsText(file);
    });

    // Copy button handler - copies rich text (formatted content)
    copyBtn.addEventListener('click', function() {
        const htmlContent = markdownOutput.innerHTML;
        const plainText = markdownOutput.innerText || markdownOutput.textContent;

        // Use modern Clipboard API with rich text support
        if (navigator.clipboard && window.ClipboardItem) {
            try {
                const htmlBlob = new Blob([htmlContent], { type: 'text/html' });
                const textBlob = new Blob([plainText], { type: 'text/plain' });

                const clipboardItem = new ClipboardItem({
                    'text/html': htmlBlob,
                    'text/plain': textBlob
                });

                navigator.clipboard.write([clipboardItem])
                    .then(() => {
                        showSuccess('Rich text copied to clipboard! You can paste it into Word, Google Docs, etc.');
                        updateCopyButton();
                    })
                    .catch((err) => {
                        console.error('Clipboard API failed:', err);
                        fallbackCopyRichText();
                    });
            } catch (err) {
                console.error('ClipboardItem failed:', err);
                fallbackCopyRichText();
            }
        } else {
            fallbackCopyRichText();
        }
    });

    // Update copy button appearance
    function updateCopyButton() {
        const originalHTML = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
        setTimeout(() => {
            copyBtn.innerHTML = originalHTML;
        }, 2000);
    }

    // Fallback copy method for rich text
    function fallbackCopyRichText() {
        // Create a temporary div with the rendered content
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = markdownOutput.innerHTML;
        tempDiv.style.position = 'fixed';
        tempDiv.style.left = '-9999px';
        tempDiv.contentEditable = true;
        document.body.appendChild(tempDiv);

        // Select the content
        const range = document.createRange();
        range.selectNodeContents(tempDiv);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);

        try {
            // Copy the selected content (preserves formatting)
            const successful = document.execCommand('copy');
            if (successful) {
                showSuccess('Rich text copied to clipboard!');
                updateCopyButton();
            } else {
                showError('Failed to copy to clipboard');
            }
        } catch (err) {
            console.error('Copy failed:', err);
            showError('Failed to copy to clipboard');
        }

        // Clean up
        selection.removeAllRanges();
        document.body.removeChild(tempDiv);
    }

    // Render Markdown function (client-side using marked.js)
    function renderMarkdown(text) {
        // Show loading state
        showLoading();

        try {
            // Small delay to show loading state
            setTimeout(() => {
                // Configure marked options
                marked.setOptions({
                    breaks: true,
                    gfm: true,
                    headerIds: true,
                    mangle: false,
                    sanitize: false // We'll use DOMPurify instead
                });

                // Parse Markdown to HTML
                const rawHtml = marked.parse(text);

                // Sanitize HTML with DOMPurify to prevent XSS
                const cleanHtml = DOMPurify.sanitize(rawHtml);

                // Update preview
                markdownOutput.innerHTML = cleanHtml;
                showPreview();
                showSuccess('Markdown rendered successfully!');
            }, 300); // Small delay for better UX

        } catch (error) {
            console.error('Error:', error);
            showError('An error occurred while rendering Markdown. Please check your syntax.');
            showEmpty();
        }
    }

    // Show loading state
    function showLoading() {
        loadingState.style.display = 'flex';
        emptyState.style.display = 'none';
        previewContent.style.display = 'none';
        copyBtn.style.display = 'none';
        hideAlerts();
    }

    // Show preview
    function showPreview() {
        loadingState.style.display = 'none';
        emptyState.style.display = 'none';
        previewContent.style.display = 'block';
        copyBtn.style.display = 'inline-block';
    }

    // Show empty state
    function showEmpty() {
        loadingState.style.display = 'none';
        emptyState.style.display = 'flex';
        previewContent.style.display = 'none';
        copyBtn.style.display = 'none';
    }

    // Show success message
    function showSuccess(message) {
        hideAlerts();
        successMessage.textContent = message;
        successAlert.style.display = 'block';
        successAlert.classList.add('show');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            successAlert.classList.remove('show');
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 150);
        }, 5000);
    }

    // Show error message
    function showError(message) {
        hideAlerts();
        errorMessage.textContent = message;
        errorAlert.style.display = 'block';
        errorAlert.classList.add('show');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            errorAlert.classList.remove('show');
            setTimeout(() => {
                errorAlert.style.display = 'none';
            }, 150);
        }, 5000);
    }

    // Hide all alerts
    function hideAlerts() {
        successAlert.classList.remove('show');
        errorAlert.classList.remove('show');
        setTimeout(() => {
            successAlert.style.display = 'none';
            errorAlert.style.display = 'none';
        }, 150);
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Handle tab switching - clear alerts
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function() {
            hideAlerts();
        });
    });
}

