// State variables
let selectedType = '';
let selectedMode = ''; // 'create' or 'edit' for flyers
let templateFile = null;
let productFiles = [];
let baseImageFiles = []; // For image editing - multiple images
let primaryImageIndex = 0; // Index of the primary image to edit

/**
 * Detect if device is mobile
 */
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
           (window.matchMedia && window.matchMedia('(max-width: 768px)').matches) ||
           ('ontouchstart' in window);
}

/**
 * Validate if file is a supported image format
 * More lenient check for mobile browsers that may report different MIME types
 */
function isValidImageFile(file) {
    if (!file) {
        console.log('[isValidImageFile] No file provided');
        return false;
    }

    var fileType = file.type ? file.type.toLowerCase() : '';
    var fileName = file.name ? file.name.toLowerCase() : '';

    console.log('[isValidImageFile] Validating file:', fileName);
    console.log('[isValidImageFile] MIME type:', fileType || '(empty)');

    // Check MIME type
    var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/heic', 'image/heif'];
    if (fileType && validTypes.includes(fileType)) {
        console.log('[isValidImageFile] ✓ Valid MIME type:', fileType);
        return true;
    }

    // Fallback: check file extension for mobile browsers that may not report correct MIME type
    var validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.avif', '.heic', '.heif'];
    var hasValidExtension = validExtensions.some(function(ext) {
        return fileName.endsWith(ext);
    });

    if (hasValidExtension) {
        console.log('[isValidImageFile] ✓ Valid file extension (MIME type was:', fileType || 'empty', ')');
        return true;
    }

    console.log('[isValidImageFile] ✗ Invalid file - MIME type:', fileType, 'Extension:', fileName.substring(fileName.lastIndexOf('.')));
    return false;
}

/**
 * Select image type (logo or flyer)
 */
function selectImageType(type) {
    selectedType = type;
    selectedMode = ''; // Reset mode when changing type

    // Update UI
    document.querySelectorAll('.image-type-card').forEach(card => {
        card.classList.remove('active');
    });

    const selectedCard = type === 'logo' ? document.getElementById('logoTypeCard') : document.getElementById('flyerTypeCard');
    selectedCard.classList.add('active');

    // Update form title
    document.getElementById('selectedTypeName').textContent = type === 'logo' ? 'Logo' : 'Advertisement Flyer';

    // Show/hide sections based on type
    if (type === 'flyer') {
        // Show mode selection for flyers
        document.getElementById('flyerModeSection').style.display = 'block';
        // Hide other sections until mode is selected
        document.getElementById('templateUploadSection').style.display = 'none';
        document.getElementById('productImagesSection').style.display = 'none';
        document.getElementById('baseImageSection').style.display = 'none';
        // Set default aspect ratio for flyers (portrait)
        document.getElementById('aspectRatioSelect').value = '2:3';
    } else {
        // For logos, no mode selection needed
        document.getElementById('flyerModeSection').style.display = 'none';
        document.getElementById('templateUploadSection').style.display = 'none';
        document.getElementById('productImagesSection').style.display = 'none';
        document.getElementById('baseImageSection').style.display = 'none';
        // Set default aspect ratio for logos (square)
        document.getElementById('aspectRatioSelect').value = '1:1';
    }

    // Show generation form
    document.getElementById('generationForm').style.display = 'block';

    // Scroll to form
    document.getElementById('generationForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * Select flyer mode (create or edit)
 */
function selectFlyerMode(mode) {
    selectedMode = mode;

    // Update UI
    document.querySelectorAll('.mode-card').forEach(card => {
        card.classList.remove('active');
    });

    const selectedCard = mode === 'create' ? document.getElementById('createModeCard') : document.getElementById('editModeCard');
    selectedCard.classList.add('active');

    // Show/hide sections based on mode
    if (mode === 'create') {
        // Create mode: show template and product uploads
        document.getElementById('templateUploadSection').style.display = 'block';
        document.getElementById('productImagesSection').style.display = 'block';
        document.getElementById('baseImageSection').style.display = 'none';

        // Update prompt placeholder and help text
        document.getElementById('promptInput').placeholder = 'Describe the flyer you want to create... (e.g., "Create a summer sale flyer with bright colors and beach theme")';
        document.getElementById('promptLabel').textContent = 'Enter Your Prompt';
        document.getElementById('promptHelpText').innerHTML = '<i class="fas fa-info-circle me-1"></i> Be specific about colors, style, and elements you want in your flyer.';
    } else {
        // Edit mode: show base image upload
        document.getElementById('templateUploadSection').style.display = 'none';
        document.getElementById('productImagesSection').style.display = 'none';
        document.getElementById('baseImageSection').style.display = 'block';

        // Update prompt placeholder and help text
        document.getElementById('promptInput').placeholder = 'Describe what you want to change... (e.g., "Change the blue background to red", "Add a 50% OFF badge in the top right corner")';
        document.getElementById('promptLabel').textContent = 'Describe Your Edits';
        document.getElementById('promptHelpText').innerHTML = '<i class="fas fa-info-circle me-1"></i> Be specific about what elements you want to add, remove, or modify in the image.';
    }
}

/**
 * Handle template file upload
 */
document.addEventListener('DOMContentLoaded', function() {
    const templateInput = document.getElementById('templateInput');
    const templateUploadArea = document.getElementById('templateUploadArea');

    console.log('[Template Upload] Initializing template upload handlers');
    console.log('[Template Upload] templateInput found:', !!templateInput);
    console.log('[Template Upload] templateUploadArea found:', !!templateUploadArea);

    if (templateInput) {
        templateInput.addEventListener('change', function(e) {
            console.log('[templateInput] Change event triggered');
            console.log('[templateInput] Files selected:', e.target.files.length);

            const file = e.target.files[0];

            if (!file) {
                console.log('[templateInput] No file selected');
                return;
            }

            console.log('[templateInput] File details:');
            console.log('[templateInput] - Name:', file.name);
            console.log('[templateInput] - Size:', Math.round(file.size / 1024), 'KB');
            console.log('[templateInput] - Type:', file.type);
            console.log('[templateInput] - Last modified:', new Date(file.lastModified).toISOString());

            var isValid = isValidImageFile(file);
            console.log('[templateInput] File validation result:', isValid);

            if (file && isValid) {
                if (file.size > 5 * 1024 * 1024) {
                    console.warn('[templateInput] File too large:', Math.round(file.size / 1024), 'KB');
                    alert('File size must be less than 5MB');
                    return;
                }
                console.log('[templateInput] File accepted, setting templateFile and calling displayTemplatePreview');
                templateFile = file;
                displayTemplatePreview(file);
            } else if (file) {
                console.error('[templateInput] Invalid file type:', file.type, 'for file:', file.name);
                alert('Please upload a valid image file (JPG, PNG, GIF, WEBP, AVIF)');
            }
        });
    } else {
        console.error('[Template Upload] templateInput element not found!');
    }

    // Drag and drop for template
    if (templateUploadArea) {
        templateUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--gold)';
        });

        templateUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d0d0d0';
        });

        templateUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d0d0d0';

            console.log('[templateUploadArea] Drop event triggered');
            console.log('[templateUploadArea] Files dropped:', e.dataTransfer.files.length);

            const file = e.dataTransfer.files[0];

            if (!file) {
                console.log('[templateUploadArea] No file in drop event');
                return;
            }

            console.log('[templateUploadArea] File details:');
            console.log('[templateUploadArea] - Name:', file.name);
            console.log('[templateUploadArea] - Size:', Math.round(file.size / 1024), 'KB');
            console.log('[templateUploadArea] - Type:', file.type);

            var isValid = isValidImageFile(file);
            console.log('[templateUploadArea] File validation result:', isValid);

            if (file && isValid) {
                if (file.size > 5 * 1024 * 1024) {
                    console.warn('[templateUploadArea] File too large:', Math.round(file.size / 1024), 'KB');
                    alert('File size must be less than 5MB');
                    return;
                }
                console.log('[templateUploadArea] File accepted, setting templateFile and calling displayTemplatePreview');
                templateFile = file;
                displayTemplatePreview(file);
            } else if (file) {
                console.error('[templateUploadArea] Invalid file type:', file.type, 'for file:', file.name);
                alert('Please upload a valid image file (JPG, PNG, GIF, WEBP, AVIF)');
            }
        });
    } else {
        console.error('[Template Upload] templateUploadArea element not found!');
    }

    // Product images upload
    var productImagesInput = document.getElementById('productImagesInput');
    var productUploadArea = document.getElementById('productUploadArea');

    console.log('[DOMContentLoaded] productImagesInput found:', !!productImagesInput);
    console.log('[DOMContentLoaded] productUploadArea found:', !!productUploadArea);

    if (productImagesInput) {
        productImagesInput.addEventListener('change', function(e) {
            console.log('[productImagesInput] Change event triggered');
            console.log('[productImagesInput] Files selected:', e.target.files.length);

            var files = Array.from(e.target.files);
            var addedCount = 0;

            files.forEach(function(file) {
                console.log('[productImagesInput] Processing file:', file.name, 'Type:', file.type, 'Size:', file.size);
                if (isValidImageFile(file)) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB: ' + file.name);
                        return;
                    }
                    productFiles.push(file);
                    addedCount++;
                    console.log('[productImagesInput] File added to productFiles');
                } else {
                    console.log('[productImagesInput] Invalid file type:', file.type);
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, or AVIF');
                }
            });

            console.log('[productImagesInput] Total productFiles now:', productFiles.length);
            console.log('[productImagesInput] Calling displayProductPreviews()');
            displayProductPreviews();
        });
    }

    // Drag and drop for product images
    if (productUploadArea) {
        productUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--gold)';
        });

        productUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d0d0d0';
        });

        productUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d0d0d0';
            console.log('[productUploadArea] Drop event triggered');

            var files = Array.from(e.dataTransfer.files);
            console.log('[productUploadArea] Files dropped:', files.length);

            files.forEach(function(file) {
                console.log('[productUploadArea] Processing file:', file.name);
                if (isValidImageFile(file)) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB: ' + file.name);
                        return;
                    }
                    productFiles.push(file);
                    console.log('[productUploadArea] File added to productFiles');
                } else {
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, or AVIF');
                }
            });

            console.log('[productUploadArea] Total productFiles now:', productFiles.length);
            displayProductPreviews();
        });
    }

    // Base image upload (for edit mode) - supports multiple images
    const baseImageInput = document.getElementById('baseImageInput');
    const baseImageUploadArea = document.getElementById('baseImageUploadArea');

    if (baseImageInput) {
        baseImageInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            files.forEach(file => {
                if (isValidImageFile(file)) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB: ' + file.name);
                        return;
                    }
                    baseImageFiles.push(file);
                } else {
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, or AVIF');
                }
            });
            displayBaseImagePreviews();
        });
    }

    // Drag and drop for base images
    if (baseImageUploadArea) {
        baseImageUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'var(--gold)';
        });

        baseImageUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d0d0d0';
        });

        baseImageUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#d0d0d0';
            const files = Array.from(e.dataTransfer.files);
            files.forEach(file => {
                if (isValidImageFile(file)) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB: ' + file.name);
                        return;
                    }
                    baseImageFiles.push(file);
                } else {
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, or AVIF');
                }
            });
            displayBaseImagePreviews();
        });
    }

    // Add touch-friendly event handlers for mode cards (mobile compatibility)
    var createModeCard = document.getElementById('createModeCard');
    var editModeCard = document.getElementById('editModeCard');

    if (createModeCard) {
        createModeCard.addEventListener('touchend', function(e) {
            e.preventDefault();
            selectFlyerMode('create');
        });
    }

    if (editModeCard) {
        editModeCard.addEventListener('touchend', function(e) {
            e.preventDefault();
            selectFlyerMode('edit');
        });
    }

    // Add touch-friendly event handlers for image type cards
    var logoTypeCard = document.getElementById('logoTypeCard');
    var flyerTypeCard = document.getElementById('flyerTypeCard');

    if (logoTypeCard) {
        logoTypeCard.addEventListener('touchend', function(e) {
            e.preventDefault();
            selectImageType('logo');
        });
    }

    if (flyerTypeCard) {
        flyerTypeCard.addEventListener('touchend', function(e) {
            e.preventDefault();
            selectImageType('flyer');
        });
    }
});

/**
 * Display template preview - Mobile compatible
 * Uses URL.createObjectURL for faster preview rendering
 */
function displayTemplatePreview(file) {
    console.log('[displayTemplatePreview] ========================================');
    console.log('[displayTemplatePreview] Processing file:', file.name);
    console.log('[displayTemplatePreview] File size:', Math.round(file.size / 1024), 'KB');
    console.log('[displayTemplatePreview] File type:', file.type);
    console.log('[displayTemplatePreview] Is mobile:', /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent));

    var uploadLabel = document.getElementById('templateUploadLabel');
    var templatePreview = document.getElementById('templatePreview');
    var templatePreviewImg = document.getElementById('templatePreviewImg');

    console.log('[displayTemplatePreview] DOM elements found:');
    console.log('[displayTemplatePreview] - uploadLabel:', !!uploadLabel);
    console.log('[displayTemplatePreview] - templatePreview:', !!templatePreview);
    console.log('[displayTemplatePreview] - templatePreviewImg:', !!templatePreviewImg);

    if (!templatePreviewImg) {
        console.error('[displayTemplatePreview] CRITICAL: templatePreviewImg element not found!');
        alert('Error: Preview element not found. Please refresh the page.');
        return;
    }

    // Hide upload label, show preview container
    if (uploadLabel) {
        uploadLabel.style.display = 'none';
        console.log('[displayTemplatePreview] Upload label hidden');
    }
    if (templatePreview) {
        templatePreview.style.display = 'block';
        console.log('[displayTemplatePreview] Preview container shown');
    }

    // Revoke old object URL if exists
    if (templatePreviewImg.src && templatePreviewImg.src.startsWith('blob:')) {
        try {
            URL.revokeObjectURL(templatePreviewImg.src);
            console.log('[displayTemplatePreview] Revoked old object URL');
        } catch (e) {
            console.warn('[displayTemplatePreview] Failed to revoke old URL:', e);
        }
    }

    // Clear any existing src to prevent cached errors
    templatePreviewImg.src = '';

    // Set up event handlers BEFORE setting src (critical for mobile)
    templatePreviewImg.onload = function() {
        console.log('[displayTemplatePreview] ✓ Template image loaded successfully');
        console.log('[displayTemplatePreview] Image dimensions:', this.naturalWidth, 'x', this.naturalHeight);
    };

    templatePreviewImg.onerror = function(errorEvent) {
        console.error('[displayTemplatePreview] ✗ Failed to load template image');
        console.error('[displayTemplatePreview] Error event:', errorEvent);
        console.error('[displayTemplatePreview] Image src:', this.src);
        console.error('[displayTemplatePreview] Image src type:', this.src.startsWith('blob:') ? 'Object URL' : 'Data URL');

        // Try fallback to FileReader if object URL failed
        if (this.src.startsWith('blob:')) {
            console.log('[displayTemplatePreview] Object URL failed, trying FileReader fallback...');
            var reader = new FileReader();
            var img = this; // Capture reference

            reader.onload = function(evt) {
                console.log('[displayTemplatePreview] FileReader loaded, data URL length:', evt.target.result.length);
                img.src = evt.target.result;
            };

            reader.onerror = function(err) {
                console.error('[displayTemplatePreview] FileReader also failed:', err);
                alert('Failed to read the image file. Please try a different image or refresh the page.');
            };

            reader.readAsDataURL(file);
        } else {
            alert('Failed to display image preview. Please try again or use a different image.');
        }
    };

    // Use object URL for preview (faster than FileReader)
    try {
        var objectUrl = URL.createObjectURL(file);
        console.log('[displayTemplatePreview] Created object URL:', objectUrl.substring(0, 50) + '...');

        // Set src AFTER handlers are attached
        templatePreviewImg.src = objectUrl;
        console.log('[displayTemplatePreview] Object URL assigned to img.src');
    } catch (e) {
        console.error('[displayTemplatePreview] Failed to create object URL:', e);
        console.log('[displayTemplatePreview] Using FileReader fallback immediately');

        // Fallback to FileReader
        var reader = new FileReader();
        reader.onload = function(evt) {
            console.log('[displayTemplatePreview] FileReader successful, data URL length:', evt.target.result.length);
            templatePreviewImg.src = evt.target.result;
        };
        reader.onerror = function(err) {
            console.error('[displayTemplatePreview] FileReader error:', err);
            alert('Failed to read the image file. Please try again.');
        };
        reader.readAsDataURL(file);
    }
}

/**
 * Remove template
 */
function removeTemplate() {
    templateFile = null;
    const templateInput = document.getElementById('templateInput');
    const uploadLabel = document.getElementById('templateUploadLabel');
    const templatePreview = document.getElementById('templatePreview');

    if (templateInput) templateInput.value = '';
    if (uploadLabel) uploadLabel.style.display = 'flex';
    if (templatePreview) templatePreview.style.display = 'none';
}

/**
 * Display base image previews (for edit mode with multiple images) - Mobile compatible
 * Uses URL.createObjectURL for faster preview rendering
 */
function displayBaseImagePreviews() {
    console.log('[displayBaseImagePreviews] Called with', baseImageFiles.length, 'files');

    var container = document.getElementById('baseImagePreviews');
    if (!container) {
        console.error('[displayBaseImagePreviews] Container not found!');
        return;
    }

    // Clear existing previews and revoke old object URLs
    var oldImages = container.querySelectorAll('img[data-object-url="true"]');
    for (var k = 0; k < oldImages.length; k++) {
        try {
            URL.revokeObjectURL(oldImages[k].src);
        } catch (e) {
            // Ignore errors
        }
    }
    container.innerHTML = '';

    if (baseImageFiles.length === 0) {
        console.log('[displayBaseImagePreviews] No files');
        return;
    }

    // Process each file using URL.createObjectURL
    for (var i = 0; i < baseImageFiles.length; i++) {
        (function(index, file) {
            console.log('[displayBaseImagePreviews] Processing file', index + 1, ':', file.name);

            var div = document.createElement('div');
            div.className = 'base-image-preview-item' + (index === primaryImageIndex ? ' primary' : '');

            // Create image element using object URL
            var img = document.createElement('img');
            img.alt = 'Image ' + (index + 1);
            img.setAttribute('data-object-url', 'true');

            try {
                var objectUrl = URL.createObjectURL(file);
                img.src = objectUrl;
                console.log('[displayBaseImagePreviews] Created object URL for file', index + 1);
            } catch (e) {
                console.error('[displayBaseImagePreviews] Failed to create object URL:', e);
                // Fallback to FileReader
                var reader = new FileReader();
                reader.onload = function(evt) {
                    img.src = evt.target.result;
                    img.setAttribute('data-object-url', 'false');
                };
                reader.readAsDataURL(file);
            }

            img.onload = function() {
                console.log('[displayBaseImagePreviews] Base image', index + 1, 'loaded successfully');
            };
            img.onerror = function() {
                console.error('[displayBaseImagePreviews] Failed to load base image', index + 1);
            };

            div.appendChild(img);

            // Add star button for primary selection
            var starDiv = document.createElement('div');
            starDiv.className = 'primary-star';
            starDiv.title = 'Set as primary image';
            starDiv.innerHTML = '<i class="fas fa-star"></i>';
            starDiv.onclick = function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                setPrimaryImage(index);
            };
            starDiv.ontouchend = function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                setPrimaryImage(index);
            };
            div.appendChild(starDiv);

            // Add remove button
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger remove-btn';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.onclick = function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                removeBaseImage(index);
            };
            removeBtn.ontouchend = function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                removeBaseImage(index);
            };
            div.appendChild(removeBtn);

            // Add label
            var labelDiv = document.createElement('div');
            labelDiv.className = 'image-label';
            labelDiv.textContent = index === primaryImageIndex ? 'Primary Image' : 'Associated Image';
            div.appendChild(labelDiv);

            container.appendChild(div);
            console.log('[displayBaseImagePreviews] Preview added for file', index + 1);
        })(i, baseImageFiles[i]);
    }
}

/**
 * Set primary image for editing
 */
function setPrimaryImage(index) {
    primaryImageIndex = index;
    displayBaseImagePreviews();
}

/**
 * Remove base image
 */
function removeBaseImage(index) {
    baseImageFiles.splice(index, 1);

    // Adjust primary index if needed
    if (primaryImageIndex >= baseImageFiles.length && baseImageFiles.length > 0) {
        primaryImageIndex = baseImageFiles.length - 1;
    } else if (baseImageFiles.length === 0) {
        primaryImageIndex = 0;
    } else if (index < primaryImageIndex) {
        primaryImageIndex--;
    } else if (index === primaryImageIndex && baseImageFiles.length > 0) {
        // If we removed the primary image, set the first one as primary
        primaryImageIndex = 0;
    }

    displayBaseImagePreviews();
}

/**
 * Display product image previews - Mobile compatible
 * Previews are shown INSIDE the upload area (inline)
 * Uses URL.createObjectURL for faster preview rendering (no FileReader needed)
 */
function displayProductPreviews() {
    console.log('[displayProductPreviews] Called with', productFiles.length, 'files');

    var container = document.getElementById('productPreviews');
    var uploadArea = document.getElementById('productUploadArea');
    var placeholder = document.getElementById('productUploadPlaceholder');
    var previewsInline = document.getElementById('productPreviewsInline');
    var productCountSpan = document.getElementById('productCount');

    console.log('[displayProductPreviews] Container found:', !!container);
    console.log('[displayProductPreviews] UploadArea found:', !!uploadArea);
    console.log('[displayProductPreviews] Placeholder found:', !!placeholder);
    console.log('[displayProductPreviews] PreviewsInline found:', !!previewsInline);

    if (!container) {
        console.error('[displayProductPreviews] Container #productPreviews not found!');
        return;
    }

    // Clear existing previews and revoke old object URLs
    var oldImages = container.querySelectorAll('img[data-object-url="true"]');
    for (var k = 0; k < oldImages.length; k++) {
        try {
            URL.revokeObjectURL(oldImages[k].src);
        } catch (e) {
            // Ignore errors when revoking
        }
    }
    container.innerHTML = '';

    // Show/hide elements based on whether there are files
    if (productFiles.length === 0) {
        console.log('[displayProductPreviews] No files, showing placeholder');
        if (placeholder) placeholder.style.display = 'flex';
        if (previewsInline) previewsInline.style.display = 'none';
        if (uploadArea) uploadArea.classList.remove('has-images');
        if (productCountSpan) {
            productCountSpan.style.display = 'none';
            productCountSpan.textContent = '0';
        }
        return;
    }

    // Hide placeholder, show inline previews
    if (placeholder) placeholder.style.display = 'none';
    if (previewsInline) previewsInline.style.display = 'flex';
    if (uploadArea) uploadArea.classList.add('has-images');

    // Update count badge
    if (productCountSpan) {
        productCountSpan.style.display = 'inline-block';
        productCountSpan.textContent = productFiles.length;
    }
    console.log('[displayProductPreviews] Showing', productFiles.length, 'preview(s) inline');

    // Process each file using URL.createObjectURL (faster than FileReader for previews)
    for (var i = 0; i < productFiles.length; i++) {
        (function(index, file) {
            console.log('[displayProductPreviews] Processing file', index + 1, ':', file.name, 'Size:', Math.round(file.size / 1024), 'KB');

            var div = document.createElement('div');
            div.className = 'product-preview-item';
            div.setAttribute('data-index', index);

            // Create image element using object URL (much faster than FileReader)
            var img = document.createElement('img');
            img.alt = 'Product ' + (index + 1);
            img.setAttribute('data-object-url', 'true');

            // Create object URL for preview
            try {
                var objectUrl = URL.createObjectURL(file);
                img.src = objectUrl;
                console.log('[displayProductPreviews] Created object URL for file', index + 1);
            } catch (e) {
                console.error('[displayProductPreviews] Failed to create object URL for file', index + 1, ':', e);
                // Fallback: try FileReader
                var reader = new FileReader();
                reader.onload = function(evt) {
                    img.src = evt.target.result;
                    img.setAttribute('data-object-url', 'false');
                };
                reader.onerror = function() {
                    console.error('[displayProductPreviews] FileReader also failed for file', index + 1);
                };
                reader.readAsDataURL(file);
            }

            img.onload = function() {
                console.log('[displayProductPreviews] Image', index + 1, 'rendered successfully');
            };
            img.onerror = function() {
                console.error('[displayProductPreviews] Failed to render image', index + 1);
            };

            div.appendChild(img);

            // Add remove button
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger remove-btn';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.setAttribute('data-index', index);

            // Use closure to capture correct index
            removeBtn.onclick = function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                console.log('[displayProductPreviews] Remove clicked for index', index);
                removeProductImage(index);
            };

            // Also add touch support
            removeBtn.ontouchend = function(evt) {
                evt.preventDefault();
                evt.stopPropagation();
                console.log('[displayProductPreviews] Remove touched for index', index);
                removeProductImage(index);
            };

            div.appendChild(removeBtn);
            container.appendChild(div);

            console.log('[displayProductPreviews] Preview added for file', index + 1);
        })(i, productFiles[i]);
    }
}

/**
 * Remove product image
 */
function removeProductImage(index) {
    console.log('[removeProductImage] Removing product at index:', index);
    console.log('[removeProductImage] Before removal, productFiles:', productFiles.length);

    if (index >= 0 && index < productFiles.length) {
        productFiles.splice(index, 1);
        console.log('[removeProductImage] After removal, productFiles:', productFiles.length);
    } else {
        console.error('[removeProductImage] Invalid index:', index);
    }

    displayProductPreviews();
}

/**
 * Generate image using Gemini Nano Banana model
 */
async function generateImage() {
    console.log('[generateImage] Starting image generation');

    var promptInput = document.getElementById('promptInput');
    var prompt = promptInput ? promptInput.value.trim() : '';

    if (!prompt) {
        alert('Please enter a prompt to generate the image.');
        return;
    }

    if (!selectedType) {
        alert('Please select an image type first.');
        return;
    }

    // Validation for flyer type - must select a mode
    if (selectedType === 'flyer' && !selectedMode) {
        alert('Please select a flyer mode (Create New or Edit Existing).');
        return;
    }

    // Validation for edit mode - must have at least one image
    if (selectedType === 'flyer' && selectedMode === 'edit') {
        if (baseImageFiles.length === 0) {
            alert('Please upload at least one image to edit.');
            return;
        }
    }

    // Validation for create mode - should have at least template or product images
    if (selectedType === 'flyer' && selectedMode === 'create') {
        if (!templateFile && productFiles.length === 0) {
            // Allow but warn the user
            console.log('[generateImage] Create mode with no images - will generate from scratch');
        }
    }

    console.log('[generateImage] Validation passed');
    console.log('[generateImage] Type:', selectedType, 'Mode:', selectedMode);
    console.log('[generateImage] Template:', templateFile ? templateFile.name : 'none');
    console.log('[generateImage] Products:', productFiles.length);

    // Show loading
    document.getElementById('generateBtn').disabled = true;
    document.getElementById('loadingContainer').style.display = 'block';
    document.getElementById('resultSection').style.display = 'none';

    try {
        // Build the complete prompt based on type and inputs
        var completePrompt = buildCompletePrompt(prompt);
        console.log('[generateImage] Complete prompt built');

        // Call Gemini Nano Banana API
        var generatedImageUrl = await callGeminiNanoBanana(completePrompt);
        console.log('[generateImage] Image URL received');

        // Display result
        document.getElementById('generatedImage').src = generatedImageUrl;
        document.getElementById('resultSection').style.display = 'block';
        console.log('[generateImage] Image displayed successfully');

    } catch (error) {
        console.error('[generateImage] Error:', error);
        // Use the getErrorMessage helper function
        var errorMessage = getErrorMessage(error);
        alert('Failed to generate image. Please try again.\n\nError: ' + errorMessage);
    } finally {
        document.getElementById('generateBtn').disabled = false;
        document.getElementById('loadingContainer').style.display = 'none';
    }
}

/**
 * Build complete prompt based on type and uploaded files
 */
function buildCompletePrompt(userPrompt) {
    let completePrompt = '';

    if (selectedType === 'logo') {
        completePrompt = `Create a professional logo. ${userPrompt}`;
    } else if (selectedType === 'flyer') {
        if (selectedMode === 'edit') {
            // Edit mode: modify existing image
            completePrompt = `Using the provided primary image, ${userPrompt}. Keep the rest of the image elements that are not mentioned unchanged, preserving the original style, lighting, and composition.`;

            // Mention associated images if any
            const associatedCount = baseImageFiles.length - 1;
            if (associatedCount > 0) {
                completePrompt += ` I have also provided ${associatedCount} additional image${associatedCount > 1 ? 's' : ''} that you can use to insert elements into the primary image as needed.`;
            }
        } else {
            // Create mode: generate new flyer
            completePrompt = `Create an advertisement flyer. ${userPrompt}`;

            if (templateFile) {
                completePrompt += ' Use the uploaded template as a base design.';
            }

            if (productFiles.length > 0) {
                completePrompt += ` Include ${productFiles.length} product image(s) in the design.`;
            }
        }
    }

    return completePrompt;
}

/**
 * Get error message safely (mobile-compatible)
 */
function getErrorMessage(error) {
    if (!error) return 'Unknown error';
    if (typeof error === 'string') return error;
    if (error.message) return error.message;
    if (error.toString && error.toString() !== '[object Object]') return error.toString();
    return 'Unknown error';
}

/**
 * Compress and resize image for mobile compatibility
 * Reduces image size to prevent memory issues on mobile devices
 */
function compressImage(file, maxWidth, maxHeight, quality) {
    maxWidth = maxWidth || 1920;
    maxHeight = maxHeight || 1920;
    quality = quality || 0.85;

    return new Promise(function(resolve, reject) {
        var fileName = file ? file.name : 'unknown';
        var fileSize = file ? Math.round(file.size / 1024) : 0;
        var fileType = file ? (file.type || 'unknown') : 'unknown';

        console.log('[compressImage] Starting compression');
        console.log('[compressImage] File name:', fileName);
        console.log('[compressImage] File size:', fileSize, 'KB');
        console.log('[compressImage] File type:', fileType);

        if (!file) {
            reject(new Error('No file provided for compression'));
            return;
        }

        // Check if file type is supported
        var supportedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
        var isHeic = fileType === 'image/heic' || fileType === 'image/heif' ||
                     fileName.toLowerCase().endsWith('.heic') || fileName.toLowerCase().endsWith('.heif');

        if (isHeic) {
            console.warn('[compressImage] HEIC/HEIF format detected - not supported for compression, using direct read');
            reject(new Error('HEIC format requires direct read'));
            return;
        }

        // Use createObjectURL for faster initial load (works better on mobile)
        var objectUrl = null;
        try {
            objectUrl = URL.createObjectURL(file);
            console.log('[compressImage] Created object URL');
        } catch (urlError) {
            console.error('[compressImage] Failed to create object URL:', urlError);
            // Fall back to FileReader approach
            objectUrl = null;
        }

        if (objectUrl) {
            // Use object URL approach (faster and more memory efficient)
            var img = new Image();

            img.onload = function() {
                console.log('[compressImage] Image loaded via objectURL, dimensions:', img.width, 'x', img.height);

                try {
                    // Release object URL after image loads
                    URL.revokeObjectURL(objectUrl);

                    var canvas = document.createElement('canvas');
                    var width = img.width;
                    var height = img.height;

                    // Calculate new dimensions
                    if (width > maxWidth || height > maxHeight) {
                        var ratio = Math.min(maxWidth / width, maxHeight / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                        console.log('[compressImage] Resizing to:', width, 'x', height);
                    }

                    canvas.width = width;
                    canvas.height = height;

                    var ctx = canvas.getContext('2d');
                    if (!ctx) {
                        reject(new Error('Failed to get canvas context'));
                        return;
                    }

                    ctx.drawImage(img, 0, 0, width, height);

                    // Use JPEG for better compression
                    var dataUrl = canvas.toDataURL('image/jpeg', quality);
                    var compressedSize = Math.round(dataUrl.length / 1024);
                    console.log('[compressImage] Compression complete, new size:', compressedSize, 'KB');

                    // Clear canvas to free memory
                    canvas.width = 0;
                    canvas.height = 0;

                    resolve(dataUrl);
                } catch (err) {
                    console.error('[compressImage] Canvas error:', err);
                    reject(new Error('Image compression failed: ' + getErrorMessage(err)));
                }
            };

            img.onerror = function(e) {
                console.error('[compressImage] Image load failed via objectURL');
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Failed to load image: ' + fileName));
            };

            img.src = objectUrl;
        } else {
            // Fallback to FileReader (slower but more compatible)
            console.log('[compressImage] Using FileReader fallback');
            var reader = new FileReader();

            reader.onload = function(e) {
                console.log('[compressImage] FileReader loaded successfully');
                var img = new Image();

                img.onload = function() {
                    console.log('[compressImage] Image loaded via FileReader, dimensions:', img.width, 'x', img.height);

                    try {
                        var canvas = document.createElement('canvas');
                        var width = img.width;
                        var height = img.height;

                        if (width > maxWidth || height > maxHeight) {
                            var ratio = Math.min(maxWidth / width, maxHeight / height);
                            width = Math.round(width * ratio);
                            height = Math.round(height * ratio);
                            console.log('[compressImage] Resizing to:', width, 'x', height);
                        }

                        canvas.width = width;
                        canvas.height = height;

                        var ctx = canvas.getContext('2d');
                        if (!ctx) {
                            reject(new Error('Failed to get canvas context'));
                            return;
                        }

                        ctx.drawImage(img, 0, 0, width, height);

                        var dataUrl = canvas.toDataURL('image/jpeg', quality);
                        console.log('[compressImage] Compression complete, new size:', Math.round(dataUrl.length / 1024), 'KB');

                        canvas.width = 0;
                        canvas.height = 0;

                        resolve(dataUrl);
                    } catch (err) {
                        console.error('[compressImage] Canvas error:', err);
                        reject(new Error('Image compression failed: ' + getErrorMessage(err)));
                    }
                };

                img.onerror = function() {
                    console.error('[compressImage] Image load failed via FileReader');
                    reject(new Error('Failed to load image: ' + fileName));
                };

                img.src = e.target.result;
            };

            reader.onerror = function(e) {
                var errorMsg = 'FileReader error';
                if (reader.error) {
                    errorMsg = reader.error.message || reader.error.name || 'Unknown FileReader error';
                }
                console.error('[compressImage] FileReader error:', errorMsg);
                reject(new Error('Failed to read file: ' + fileName + ' (' + errorMsg + ')'));
            };

            reader.onabort = function() {
                console.error('[compressImage] FileReader aborted');
                reject(new Error('File reading was aborted: ' + fileName));
            };

            reader.readAsDataURL(file);
        }
    });
}

/**
 * Convert file to base64 data URL
 * Preserves original image quality - NO compression or resizing
 * Uses ES5-compatible syntax for older mobile browsers
 */
function fileToBase64(file) {
    return new Promise(function(resolve, reject) {
        var fileName = file ? file.name : 'null';
        var fileSize = file ? file.size : 0;
        var fileSizeKB = Math.round(fileSize / 1024);
        var fileSizeMB = (fileSize / (1024 * 1024)).toFixed(2);
        var fileType = file ? (file.type || 'unknown') : 'unknown';

        console.log('[fileToBase64] ========================================');
        console.log('[fileToBase64] Processing file:', fileName);
        console.log('[fileToBase64] File size:', fileSizeKB, 'KB (' + fileSizeMB + ' MB)');
        console.log('[fileToBase64] File type:', fileType);
        console.log('[fileToBase64] File object valid:', !!file);
        console.log('[fileToBase64] Mode: DIRECT READ (no compression)');

        if (!file) {
            console.error('[fileToBase64] No file provided!');
            reject(new Error('No file provided for conversion'));
            return;
        }

        // Check if file size is 0 (corrupted or invalid reference)
        if (fileSize === 0) {
            console.error('[fileToBase64] File size is 0 - file may be corrupted or reference invalid');
            reject(new Error('File appears to be empty or corrupted: ' + fileName));
            return;
        }

        // Log large file warning but still process at original quality
        if (fileSize > 5 * 1024 * 1024) {
            console.warn('[fileToBase64] Large file detected (' + fileSizeMB + ' MB) - sending original quality to AI');
        }

        // Log device type for debugging
        var isMobile = isMobileDevice();
        console.log('[fileToBase64] Is mobile device:', isMobile);

        // Always use direct read - preserve original image quality for AI processing
        console.log('[fileToBase64] Using direct read - preserving original image quality');
        directFileRead(file, resolve, reject);
    });
}

/**
 * Direct file read without compression (fallback)
 * Enhanced error handling for mobile browsers
 */
function directFileRead(file, resolve, reject) {
    var fileName = file ? file.name : 'unknown';
    var fileSize = file ? file.size : 0;

    console.log('[directFileRead] Starting direct read for:', fileName);
    console.log('[directFileRead] File size:', Math.round(fileSize / 1024), 'KB');

    if (!file || fileSize === 0) {
        console.error('[directFileRead] Invalid file or zero size');
        reject(new Error('Invalid file: ' + fileName));
        return;
    }

    // Try using object URL first (faster and more memory efficient on mobile)
    var objectUrl = null;
    try {
        objectUrl = URL.createObjectURL(file);
        console.log('[directFileRead] Created object URL for faster loading');
    } catch (e) {
        console.warn('[directFileRead] Could not create object URL:', e);
        objectUrl = null;
    }

    if (objectUrl) {
        // Use object URL with Image to verify the file is valid, then read
        var testImg = new Image();
        testImg.onload = function() {
            console.log('[directFileRead] File validated via Image, now reading as base64...');
            URL.revokeObjectURL(objectUrl);

            // Now do the actual FileReader
            var reader = new FileReader();

            reader.onload = function() {
                console.log('[directFileRead] Read successful, size:', Math.round(reader.result.length / 1024), 'KB');
                if (reader.result) {
                    resolve(reader.result);
                } else {
                    console.error('[directFileRead] Read returned empty result');
                    reject(new Error('Failed to read file data: ' + fileName));
                }
            };

            reader.onerror = function(e) {
                var errorMsg = 'Unknown error';
                if (reader.error) {
                    errorMsg = reader.error.message || reader.error.name || 'FileReader error';
                }
                console.error('[directFileRead] FileReader error:', errorMsg);
                reject(new Error('Failed to read file ' + fileName + ': ' + errorMsg));
            };

            reader.onabort = function() {
                console.error('[directFileRead] FileReader aborted');
                reject(new Error('File reading was aborted: ' + fileName));
            };

            reader.readAsDataURL(file);
        };

        testImg.onerror = function() {
            console.warn('[directFileRead] Image validation failed, trying direct FileReader');
            URL.revokeObjectURL(objectUrl);

            // Fallback to direct FileReader without validation
            readFileDirectly(file, resolve, reject, fileName);
        };

        testImg.src = objectUrl;
    } else {
        // Direct FileReader fallback
        readFileDirectly(file, resolve, reject, fileName);
    }
}

/**
 * Simple direct FileReader (innermost fallback)
 */
function readFileDirectly(file, resolve, reject, fileName) {
    console.log('[readFileDirectly] Using simple FileReader for:', fileName);

    var reader = new FileReader();

    reader.onload = function() {
        console.log('[readFileDirectly] Read successful');
        if (reader.result) {
            console.log('[readFileDirectly] Result size:', Math.round(reader.result.length / 1024), 'KB');
            resolve(reader.result);
        } else {
            console.error('[readFileDirectly] Empty result');
            reject(new Error('Failed to read file data: ' + fileName));
        }
    };

    reader.onerror = function(e) {
        var errorMsg = 'Unknown error';
        if (reader.error) {
            errorMsg = reader.error.message || reader.error.name || 'FileReader error';
        }
        console.error('[readFileDirectly] FileReader error:', errorMsg);
        console.error('[readFileDirectly] Error object:', reader.error);
        reject(new Error('Failed to read file ' + fileName + ': ' + errorMsg));
    };

    reader.onabort = function() {
        console.error('[readFileDirectly] Aborted');
        reject(new Error('File reading was aborted: ' + fileName));
    };

    try {
        reader.readAsDataURL(file);
    } catch (e) {
        console.error('[readFileDirectly] Exception calling readAsDataURL:', e);
        reject(new Error('Exception reading file ' + fileName + ': ' + getErrorMessage(e)));
    }
}

/**
 * Call OpenRouter API with Google Gemini 2.5 Flash Image model (Nano Banana)
 * This model generates actual images, not just descriptions
 * Supports both image generation and image editing
 * Uses ES5-compatible syntax for mobile browser support
 */
async function callGeminiNanoBanana(prompt) {
    console.log('[callGeminiNanoBanana] Starting API call');
    console.log('[callGeminiNanoBanana] Prompt:', prompt);
    console.log('[callGeminiNanoBanana] Mode:', selectedMode);
    console.log('[callGeminiNanoBanana] Base image files count:', baseImageFiles.length);
    console.log('[callGeminiNanoBanana] Product files count:', productFiles.length);
    console.log('[callGeminiNanoBanana] Template file:', templateFile ? 'yes' : 'no');

    try {
        // Get selected aspect ratio from dropdown
        var aspectRatioSelect = document.getElementById('aspectRatioSelect');
        var aspectRatio = aspectRatioSelect ? aspectRatioSelect.value : "1:1";
        console.log('[callGeminiNanoBanana] Aspect ratio:', aspectRatio);

        // Build the message content array
        var contentParts = [];

        // For edit mode, include the primary base image first, then associated images
        if (selectedMode === 'edit' && baseImageFiles.length > 0) {
            console.log('[callGeminiNanoBanana] Processing edit mode images...');

            // Add primary image first
            try {
                console.log('[callGeminiNanoBanana] Converting primary image...');
                var primaryImageBase64 = await fileToBase64(baseImageFiles[primaryImageIndex]);
                console.log('[callGeminiNanoBanana] Primary image converted, size:', Math.round(primaryImageBase64.length / 1024), 'KB');
                contentParts.push({
                    type: "image_url",
                    image_url: {
                        url: primaryImageBase64
                    }
                });
            } catch (imgError) {
                console.error('[callGeminiNanoBanana] Primary image conversion failed:', imgError);
                throw new Error('Failed to process primary image: ' + getErrorMessage(imgError));
            }

            // Add associated images (all images except the primary one)
            for (var i = 0; i < baseImageFiles.length; i++) {
                if (i !== primaryImageIndex) {
                    try {
                        console.log('[callGeminiNanoBanana] Converting associated image', i);
                        var associatedImageBase64 = await fileToBase64(baseImageFiles[i]);
                        contentParts.push({
                            type: "image_url",
                            image_url: {
                                url: associatedImageBase64
                            }
                        });
                    } catch (imgError) {
                        console.error('[callGeminiNanoBanana] Associated image conversion failed:', imgError);
                        throw new Error('Failed to process associated image: ' + getErrorMessage(imgError));
                    }
                }
            }
        }

        // For create mode with template, include template image
        if (selectedMode === 'create' && templateFile) {
            try {
                console.log('[callGeminiNanoBanana] Converting template image...');
                var templateBase64 = await fileToBase64(templateFile);
                console.log('[callGeminiNanoBanana] Template converted, size:', Math.round(templateBase64.length / 1024), 'KB');
                contentParts.push({
                    type: "image_url",
                    image_url: {
                        url: templateBase64
                    }
                });
            } catch (imgError) {
                console.error('[callGeminiNanoBanana] Template conversion failed:', imgError);
                throw new Error('Failed to process template image: ' + getErrorMessage(imgError));
            }
        }

        // Add product images if any (for create mode)
        if (selectedMode === 'create' && productFiles.length > 0) {
            console.log('[callGeminiNanoBanana] Processing', productFiles.length, 'product images...');
            for (var j = 0; j < productFiles.length; j++) {
                try {
                    console.log('[callGeminiNanoBanana] Converting product image', j + 1);
                    var productBase64 = await fileToBase64(productFiles[j]);
                    contentParts.push({
                        type: "image_url",
                        image_url: {
                            url: productBase64
                        }
                    });
                } catch (imgError) {
                    console.error('[callGeminiNanoBanana] Product image conversion failed:', imgError);
                    throw new Error('Failed to process product image ' + (j + 1) + ': ' + getErrorMessage(imgError));
                }
            }
        }

        // Add the text prompt
        contentParts.push({
            type: "text",
            text: prompt
        });

        // Count how many images are in contentParts
        var imageCount = 0;
        for (var k = 0; k < contentParts.length; k++) {
            if (contentParts[k].type === 'image_url') {
                imageCount++;
            }
        }
        console.log('[callGeminiNanoBanana] Content parts ready:', contentParts.length, 'parts');
        console.log('[callGeminiNanoBanana] Images included:', imageCount);
        console.log('[callGeminiNanoBanana] Text prompts:', contentParts.length - imageCount);

        // Prepare the API request body
        var requestBody = {
            model: "google/gemini-2.5-flash-image",
            messages: [
                {
                    role: "user",
                    content: contentParts
                }
            ],
            modalities: ["image", "text"],
            image_config: {
                aspect_ratio: aspectRatio
            }
        };

        // Get CSRF token
        var csrfTokenName = '<?= csrf_token() ?>';
        var csrfInput = document.querySelector('input[name="' + csrfTokenName + '"]');
        if (!csrfInput) {
            throw new Error('CSRF token input not found');
        }
        var csrfToken = csrfInput.value;

        // Build request body with CSRF (ES5 compatible - no spread operator)
        var bodyWithCsrf = JSON.parse(JSON.stringify(requestBody));
        bodyWithCsrf[csrfTokenName] = csrfToken;

        console.log('[callGeminiNanoBanana] Making API request...');
        var requestBodyString = JSON.stringify(bodyWithCsrf);
        console.log('[callGeminiNanoBanana] Request body size:', Math.round(requestBodyString.length / 1024), 'KB');

        // Make API call to backend endpoint
        var response;
        try {
            response = await fetch('<?= base_url("image-creator/generate") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: requestBodyString
            });
            console.log('[callGeminiNanoBanana] Fetch completed, status:', response.status);
        } catch (networkError) {
            console.error('[callGeminiNanoBanana] Network error:', networkError);
            throw new Error('Network error: Please check your internet connection and try again.');
        }

        // Parse response
        var responseData;
        try {
            var responseText = await response.text();
            console.log('[callGeminiNanoBanana] Response text length:', responseText.length);
            responseData = JSON.parse(responseText);
        } catch (parseError) {
            console.error('[callGeminiNanoBanana] Response parse error:', parseError);
            throw new Error('Server response error. Please try again.');
        }

        // Update CSRF token
        if (responseData && responseData.csrf_token) {
            var newCsrfInput = document.querySelector('input[name="' + csrfTokenName + '"]');
            if (newCsrfInput) {
                newCsrfInput.value = responseData.csrf_token;
            }
        }

        // Check for success
        if (!responseData || !responseData.success) {
            var errorMsg = 'API request failed';
            if (responseData && responseData.error) {
                errorMsg = responseData.error;
            }
            console.error('[callGeminiNanoBanana] API error:', errorMsg);
            throw new Error(errorMsg);
        }

        var data = responseData.data;
        console.log('[callGeminiNanoBanana] API Response received');

        // Extract the generated image from the response
        if (data && data.choices && data.choices[0] && data.choices[0].message) {
            var message = data.choices[0].message;

            // Check if images were generated
            if (message.images && message.images.length > 0) {
                // Return the first generated image (base64 data URL)
                // ES5 compatible - no optional chaining
                var imageData = message.images[0];
                var imageUrl = null;
                if (imageData.image_url && imageData.image_url.url) {
                    imageUrl = imageData.image_url.url;
                } else if (imageData.url) {
                    imageUrl = imageData.url;
                }

                if (!imageUrl) {
                    throw new Error('Image URL not found in response');
                }
                console.log('[callGeminiNanoBanana] Generated image received successfully');
                return imageUrl;
            }
            // If no images but there's text content
            else if (message.content) {
                console.log('[callGeminiNanoBanana] Text response:', message.content);
                var truncatedContent = message.content.length > 100 ? message.content.substring(0, 100) : message.content;
                throw new Error('Model returned text instead of image: ' + truncatedContent);
            }
            else {
                throw new Error('No image generated in the response');
            }
        } else {
            console.error('[callGeminiNanoBanana] Unexpected response format:', data);
            throw new Error('Unexpected API response format');
        }

    } catch (error) {
        console.error('[callGeminiNanoBanana] Error:', error);
        // Ensure we always throw an error with a message
        if (error && error.message) {
            throw error;
        } else {
            throw new Error('An unexpected error occurred during image generation');
        }
    }
}

/**
 * Download generated image
 */
function downloadImage() {
    const img = document.getElementById('generatedImage');
    const link = document.createElement('a');
    link.href = img.src;
    link.download = `generated-${selectedType}-${Date.now()}.png`;
    link.click();
}

/**
 * Reset form to create another image
 */
function resetForm() {
    // Clear inputs
    document.getElementById('promptInput').value = '';
    templateFile = null;
    productFiles = [];
    baseImageFiles = [];
    primaryImageIndex = 0;

    // Reset file inputs
    document.getElementById('templateInput').value = '';
    if (document.getElementById('productImagesInput')) {
        document.getElementById('productImagesInput').value = '';
    }
    if (document.getElementById('baseImageInput')) {
        document.getElementById('baseImageInput').value = '';
    }

    // Reset previews
    const templateUploadArea = document.getElementById('templateUploadArea');
    if (templateUploadArea && templateUploadArea.querySelector('.upload-placeholder')) {
        templateUploadArea.querySelector('.upload-placeholder').style.display = 'block';
    }
    if (document.getElementById('templatePreview')) {
        document.getElementById('templatePreview').style.display = 'none';
    }
    if (document.getElementById('productPreviews')) {
        document.getElementById('productPreviews').innerHTML = '';
    }

    // Reset base image previews
    if (document.getElementById('baseImagePreviews')) {
        document.getElementById('baseImagePreviews').innerHTML = '';
    }

    // Hide result
    document.getElementById('resultSection').style.display = 'none';

    // Scroll to top of form
    document.getElementById('generationForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
