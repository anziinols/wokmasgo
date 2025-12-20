// Clear browser storage on page load to prevent stale file references
(function() {
    try {
        // Clear localStorage
        const localKeysToRemove = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && (key.startsWith('imageCreator') || key.startsWith('image_creator') || key.startsWith('flyer') || key.startsWith('logo'))) {
                localKeysToRemove.push(key);
            }
        }
        localKeysToRemove.forEach(key => localStorage.removeItem(key));

        // Clear sessionStorage
        const sessionKeysToRemove = [];
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key && (key.startsWith('imageCreator') || key.startsWith('image_creator') || key.startsWith('flyer') || key.startsWith('logo'))) {
                sessionKeysToRemove.push(key);
            }
        }
        sessionKeysToRemove.forEach(key => sessionStorage.removeItem(key));
    } catch (e) {
        console.log('Error clearing storage on page load:', e);
    }
})();

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
 * Validate if file is a supported image format - mobile friendly
 * Includes common phone formats like HEIC/HEIF in addition to JPG/PNG/etc.
 */
function isValidImageFile(file) {
    if (!file) return false;

    var fileType = file.type ? file.type.toLowerCase() : '';
    var fileName = file.name ? file.name.toLowerCase() : '';

    // Check MIME type first
    var validTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/avif',
        'image/heic',
        'image/heif'
    ];
    if (fileType && validTypes.indexOf(fileType) !== -1) {
        return true;
    }

    // Fallback: check file extension
    var validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.avif', '.heic', '.heif'];
    return validExtensions.some(function(ext) {
        return fileName.endsWith(ext);
    });
}

/**
 * Check if file is HEIC/HEIF format (iPhone photos)
 */
function isHeicFile(file) {
    if (!file) return false;
    var type = file.type ? file.type.toLowerCase() : '';
    var name = file.name ? file.name.toLowerCase() : '';
    return type === 'image/heic' || type === 'image/heif' ||
           name.endsWith('.heic') || name.endsWith('.heif');
}

/**
 * Create a preview data URL for image
 * Uses FileReader for better mobile compatibility
 * Converts HEIC to JPEG for browser display
 * Returns a Promise that resolves to {dataUrl, file}
 */
function createPreviewUrl(file) {
    return new Promise(function(resolve, reject) {
        if (!file) {
            reject(new Error('No file provided'));
            return;
        }

        console.log('[createPreviewUrl] Processing:', file.name, 'Type:', file.type, 'Size:', file.size);

        // Check if HEIC file - needs conversion
        if (isHeicFile(file)) {
            console.log('[createPreviewUrl] HEIC file detected, converting to JPEG...');

            if (typeof heic2any === 'undefined') {
                console.error('[createPreviewUrl] heic2any library not loaded');
                reject(new Error('HEIC conversion library not available'));
                return;
            }

            heic2any({
                blob: file,
                toType: 'image/jpeg',
                quality: 0.8
            }).then(function(convertedBlob) {
                console.log('[createPreviewUrl] HEIC converted successfully');
                // Read converted blob as data URL
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Create a new File from converted blob
                    var convertedFile = new File([convertedBlob], file.name.replace(/\.heic$/i, '.jpg').replace(/\.heif$/i, '.jpg'), {
                        type: 'image/jpeg'
                    });
                    resolve({ dataUrl: e.target.result, file: convertedFile });
                };
                reader.onerror = function() {
                    reject(new Error('Failed to read converted HEIC file'));
                };
                reader.readAsDataURL(convertedBlob);
            }).catch(function(err) {
                console.error('[createPreviewUrl] HEIC conversion failed:', err);
                reject(err);
            });
        } else {
            // Standard image - read directly as data URL
            var reader = new FileReader();
            reader.onload = function(e) {
                console.log('[createPreviewUrl] File read successfully');
                resolve({ dataUrl: e.target.result, file: file });
            };
            reader.onerror = function() {
                console.error('[createPreviewUrl] FileReader error');
                reject(new Error('Failed to read file'));
            };
            reader.readAsDataURL(file);
        }
    });
}

// Storage for processed previews (data URLs)
var productPreviews = []; // Array of {file, dataUrl}
var baseImagePreviews = []; // Array of {file, dataUrl}

/**
 * Show template loading state
 */
function showTemplateLoading() {
    var uploadLabel = document.getElementById('templateUploadLabel');
    var templatePreview = document.getElementById('templatePreview');
    if (uploadLabel) uploadLabel.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
}

/**
 * Hide template loading state
 */
function hideTemplateLoading() {
    var uploadLabel = document.getElementById('templateUploadLabel');
    if (uploadLabel) {
        uploadLabel.innerHTML = '<i class="fas fa-cloud-upload-alt"></i><span>Click to upload or drag and drop</span><span class="file-hint">JPG, PNG, GIF, WEBP, AVIF, HEIC (Max 5MB)</span>';
    }
}

/**
 * Process multiple product files and create previews
 */
function processProductFiles(files) {
    console.log('[processProductFiles] Processing', files.length, 'files');

    var promises = files.map(function(file) {
        return createPreviewUrl(file);
    });

    Promise.all(promises).then(function(results) {
        results.forEach(function(result) {
            productFiles.push(result.file);
            productPreviews.push({ file: result.file, dataUrl: result.dataUrl });
        });
        displayProductPreviews();
    }).catch(function(err) {
        console.error('[processProductFiles] Error:', err);
        alert('Failed to process one or more images.');
    });
}

/**
 * Process multiple base image files and create previews
 */
function processBaseImageFiles(files) {
    console.log('[processBaseImageFiles] Processing', files.length, 'files');

    var promises = files.map(function(file) {
        return createPreviewUrl(file);
    });

    Promise.all(promises).then(function(results) {
        results.forEach(function(result) {
            baseImageFiles.push(result.file);
            baseImagePreviews.push({ file: result.file, dataUrl: result.dataUrl });
        });
        displayBaseImagePreviews();
    }).catch(function(err) {
        console.error('[processBaseImageFiles] Error:', err);
        alert('Failed to process one or more images.');
    });
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
 * Initialize page state from hidden inputs
 */
document.addEventListener('DOMContentLoaded', function() {
    // Read image type and mode from hidden inputs if they exist
    const imageTypeInput = document.getElementById('imageType');
    const imageModeInput = document.getElementById('imageMode');

    if (imageTypeInput && imageTypeInput.value) {
        selectedType = imageTypeInput.value;
        console.log('[Init] Image type set to:', selectedType);
    }

    if (imageModeInput && imageModeInput.value) {
        selectedMode = imageModeInput.value;
        console.log('[Init] Image mode set to:', selectedMode);
    }
});

/**
 * Handle template file upload
 */
document.addEventListener('DOMContentLoaded', function() {
    var templateInput = document.getElementById('templateInput');
    var templateUploadArea = document.getElementById('templateUploadArea');

    if (templateInput) {
        templateInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;

            if (!isValidImageFile(file)) {
                alert('Please upload a valid image file (JPG, PNG, GIF, WEBP, AVIF, HEIC/HEIF)');
                e.target.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                e.target.value = '';
                return;
            }

            // Show loading state
            showTemplateLoading();

            // Create preview (handles HEIC conversion if needed)
            createPreviewUrl(file).then(function(result) {
                templateFile = result.file;
                displayTemplatePreview(result.dataUrl, result.file.name);
            }).catch(function(err) {
                console.error('[Template Upload] Error:', err);
                alert('Failed to process image. Please try another file.');
                hideTemplateLoading();
            });
        });
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

            var file = e.dataTransfer.files[0];
            if (!file) return;

            if (!isValidImageFile(file)) {
                alert('Please upload a valid image file (JPG, PNG, GIF, WEBP, AVIF, HEIC/HEIF)');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }

            // Show loading state
            showTemplateLoading();

            // Create preview (handles HEIC conversion if needed)
            createPreviewUrl(file).then(function(result) {
                templateFile = result.file;
                displayTemplatePreview(result.dataUrl, result.file.name);
            }).catch(function(err) {
                console.error('[Template Drop] Error:', err);
                alert('Failed to process image. Please try another file.');
                hideTemplateLoading();
            });
        });
    }

    // Product images upload
    var productImagesInput = document.getElementById('productImagesInput');
    var productUploadArea = document.getElementById('productUploadArea');

    if (productImagesInput) {
        productImagesInput.addEventListener('change', function(e) {
            var files = Array.from(e.target.files);
            if (files.length === 0) return;

            // Validate files first
            var validFiles = [];
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (!isValidImageFile(file)) {
                    alert('Invalid file type: ' + file.name);
                    continue;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB: ' + file.name);
                    continue;
                }
                validFiles.push(file);
            }

            if (validFiles.length === 0) return;

            // Process files and create previews
            processProductFiles(validFiles);
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

            var files = Array.from(e.dataTransfer.files);
            if (files.length === 0) return;

            // Validate files first
            var validFiles = [];
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (!isValidImageFile(file)) {
                    alert('Invalid file type: ' + file.name);
                    continue;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB: ' + file.name);
                    continue;
                }
                validFiles.push(file);
            }

            if (validFiles.length === 0) return;

            // Process files and create previews
            processProductFiles(validFiles);
        });
    }

    // Base image upload (for edit mode) - supports multiple images
    var baseImageInput = document.getElementById('baseImageInput');
    var baseImageUploadArea = document.getElementById('baseImageUploadArea');

    if (baseImageInput) {
        baseImageInput.addEventListener('change', function(e) {
            var files = Array.from(e.target.files);
            if (files.length === 0) return;

            // Validate files first
            var validFiles = [];
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (!isValidImageFile(file)) {
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, AVIF, or HEIC/HEIF');
                    continue;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB: ' + file.name);
                    continue;
                }
                validFiles.push(file);
            }

            if (validFiles.length === 0) return;

            // Process files and create previews
            processBaseImageFiles(validFiles);
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

            var files = Array.from(e.dataTransfer.files);
            if (files.length === 0) return;

            // Validate files first
            var validFiles = [];
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (!isValidImageFile(file)) {
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, AVIF, or HEIC/HEIF');
                    continue;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB: ' + file.name);
                    continue;
                }
                validFiles.push(file);
            }

            if (validFiles.length === 0) return;

            // Process files and create previews
            processBaseImageFiles(validFiles);
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
 * Display template preview using blob URL
 */
function displayTemplatePreview(previewUrl, fileName) {
    var uploadLabel = document.getElementById('templateUploadLabel');
    var templatePreview = document.getElementById('templatePreview');
    var templatePreviewImg = document.getElementById('templatePreviewImg');

    if (!templatePreviewImg) return;

    // Hide upload label, show preview container
    if (uploadLabel) uploadLabel.style.display = 'none';
    if (templatePreview) templatePreview.style.display = 'block';

    // Set the image source
    templatePreviewImg.src = previewUrl;
    templatePreviewImg.alt = fileName;
}

/**
 * Remove template
 */
function removeTemplate() {
    templateFile = null;
    var templateInput = document.getElementById('templateInput');
    var uploadLabel = document.getElementById('templateUploadLabel');
    var templatePreview = document.getElementById('templatePreview');

    if (templateInput) templateInput.value = '';
    if (uploadLabel) {
        uploadLabel.style.display = 'flex';
        hideTemplateLoading(); // Reset the label text
    }
    if (templatePreview) templatePreview.style.display = 'none';
}

/**
 * Display base image previews using stored data URLs
 */
function displayBaseImagePreviews() {
    var container = document.getElementById('baseImagePreviews');
    if (!container) return;

    container.innerHTML = '';

    if (baseImagePreviews.length === 0) return;

    // Create preview for each image using stored data URLs
    baseImagePreviews.forEach(function(preview, index) {
        var div = document.createElement('div');
        div.className = 'base-image-preview-item' + (index === primaryImageIndex ? ' primary' : '');

        var img = document.createElement('img');
        img.alt = preview.file.name;
        img.src = preview.dataUrl;
        div.appendChild(img);

        // Add star button for primary selection
        var starDiv = document.createElement('div');
        starDiv.className = 'primary-star';
        starDiv.title = 'Set as primary image';
        starDiv.innerHTML = '<i class="fas fa-star"></i>';
        starDiv.onclick = function() {
            setPrimaryImage(index);
        };
        div.appendChild(starDiv);

        // Add remove button
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-danger remove-btn';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.onclick = function() {
            removeBaseImage(index);
        };
        div.appendChild(removeBtn);

        // Add label
        var labelDiv = document.createElement('div');
        labelDiv.className = 'image-label';
        labelDiv.textContent = index === primaryImageIndex ? 'Primary Image' : 'Associated Image';
        div.appendChild(labelDiv);

        container.appendChild(div);
    });
}

/**
 * Set primary image for editing
 */
function setPrimaryImage(index) {
    primaryImageIndex = index;
    var container = document.getElementById('baseImagePreviews');
    if (container) {
        var items = container.querySelectorAll('.base-image-preview-item');
        items.forEach(function(item, i) {
            if (i === index) {
                item.classList.add('primary');
                var label = item.querySelector('.image-label');
                if (label) label.textContent = 'Primary Image';
            } else {
                item.classList.remove('primary');
                var label = item.querySelector('.image-label');
                if (label) label.textContent = 'Associated Image';
            }
        });
    }
}

/**
 * Remove base image
 */
function removeBaseImage(index) {
    baseImageFiles.splice(index, 1);
    baseImagePreviews.splice(index, 1);

    // Adjust primary index if needed
    if (primaryImageIndex >= baseImageFiles.length && baseImageFiles.length > 0) {
        primaryImageIndex = baseImageFiles.length - 1;
    } else if (baseImageFiles.length === 0) {
        primaryImageIndex = 0;
    } else if (index < primaryImageIndex) {
        primaryImageIndex--;
    } else if (index === primaryImageIndex && baseImageFiles.length > 0) {
        primaryImageIndex = 0;
    }

    displayBaseImagePreviews();
}

/**
 * Display product image previews using stored data URLs
 */
function displayProductPreviews() {
    var container = document.getElementById('productPreviews');
    var uploadArea = document.getElementById('productUploadArea');
    var placeholder = document.getElementById('productUploadPlaceholder');
    var previewsInline = document.getElementById('productPreviewsInline');
    var productCountSpan = document.getElementById('productCount');

    if (!container) return;

    container.innerHTML = '';

    var totalFiles = productPreviews.length;

    // Show/hide elements based on whether there are files
    if (totalFiles === 0) {
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
        productCountSpan.textContent = totalFiles;
    }

    // Create preview for each file using stored data URLs
    productPreviews.forEach(function(preview, index) {
        var div = document.createElement('div');
        div.className = 'product-preview-item';
        div.setAttribute('data-index', index);

        var img = document.createElement('img');
        img.alt = preview.file.name;
        img.src = preview.dataUrl;
        div.appendChild(img);

        // Add remove button
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger btn-sm remove-btn';
        removeBtn.innerHTML = '<i class="fas fa-times"></i>';
        removeBtn.onclick = function() {
            removeProductImage(index);
        };
        div.appendChild(removeBtn);

        container.appendChild(div);
    });
}

/**
 * Remove product image and rebuild previews
 */
function removeProductImage(index) {
    if (index >= 0 && index < productFiles.length) {
        productFiles.splice(index, 1);
        productPreviews.splice(index, 1);
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

