// State variables
let selectedType = '';
let selectedMode = ''; // 'create' or 'edit' for flyers
let templateFile = null;
let productFiles = [];
let baseImageFiles = []; // For image editing - multiple images
let primaryImageIndex = 0; // Index of the primary image to edit

/**
 * Validate if file is a supported image format
 */
function isValidImageFile(file) {
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
    return file && validTypes.includes(file.type);
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

    if (templateInput) {
        templateInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && isValidImageFile(file)) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    return;
                }
                templateFile = file;
                displayTemplatePreview(file);
            } else if (file) {
                alert('Please upload a valid image file (JPG, PNG, GIF, WEBP, AVIF)');
            }
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
            const file = e.dataTransfer.files[0];
            if (file && isValidImageFile(file)) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    return;
                }
                templateFile = file;
                displayTemplatePreview(file);
            } else if (file) {
                alert('Please upload a valid image file (JPG, PNG, GIF, WEBP, AVIF)');
            }
        });
    }

    // Product images upload
    const productImagesInput = document.getElementById('productImagesInput');
    const productUploadArea = document.getElementById('productUploadArea');

    if (productImagesInput) {
        productImagesInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            files.forEach(file => {
                if (isValidImageFile(file)) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB: ' + file.name);
                        return;
                    }
                    productFiles.push(file);
                } else {
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, or AVIF');
                }
            });
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
            const files = Array.from(e.dataTransfer.files);
            files.forEach(file => {
                if (isValidImageFile(file)) {
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size must be less than 5MB: ' + file.name);
                        return;
                    }
                    productFiles.push(file);
                } else {
                    alert('Invalid file type: ' + file.name + '. Please upload JPG, PNG, GIF, WEBP, or AVIF');
                }
            });
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
});

/**
 * Display template preview
 */
function displayTemplatePreview(file) {
    const reader = new FileReader();
    reader.onload = function(e) {
        document.querySelector('.upload-placeholder').style.display = 'none';
        document.getElementById('templatePreview').style.display = 'block';
        document.getElementById('templatePreviewImg').src = e.target.result;
    };
    reader.readAsDataURL(file);
}

/**
 * Remove template
 */
function removeTemplate() {
    templateFile = null;
    document.getElementById('templateInput').value = '';
    document.querySelector('.upload-placeholder').style.display = 'block';
    document.getElementById('templatePreview').style.display = 'none';
}

/**
 * Display base image previews (for edit mode with multiple images)
 */
function displayBaseImagePreviews() {
    const container = document.getElementById('baseImagePreviews');
    container.innerHTML = '';

    baseImageFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'base-image-preview-item' + (index === primaryImageIndex ? ' primary' : '');
            div.innerHTML = `
                <img src="${e.target.result}" alt="Image ${index + 1}">
                <div class="primary-star" onclick="setPrimaryImage(${index})" title="Set as primary image">
                    <i class="fas fa-star"></i>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeBaseImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
                <div class="image-label">
                    ${index === primaryImageIndex ? 'Primary Image' : 'Associated Image'}
                </div>
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
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
 * Display product image previews
 */
function displayProductPreviews() {
    const container = document.getElementById('productPreviews');
    container.innerHTML = '';

    productFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'product-preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Product ${index + 1}">
                <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeProductImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

/**
 * Remove product image
 */
function removeProductImage(index) {
    productFiles.splice(index, 1);
    displayProductPreviews();
}

/**
 * Generate image using Gemini Nano Banana model
 */
async function generateImage() {
    const prompt = document.getElementById('promptInput').value.trim();

    if (!prompt) {
        alert('Please enter a prompt to generate the image.');
        return;
    }

    if (!selectedType) {
        alert('Please select an image type first.');
        return;
    }

    // Validation for edit mode
    if (selectedType === 'flyer' && selectedMode === 'edit') {
        if (baseImageFiles.length === 0) {
            alert('Please upload at least one image to edit.');
            return;
        }
    }

    // Show loading
    document.getElementById('generateBtn').disabled = true;
    document.getElementById('loadingContainer').style.display = 'block';
    document.getElementById('resultSection').style.display = 'none';

    try {
        // Build the complete prompt based on type and inputs
        let completePrompt = buildCompletePrompt(prompt);

        // Call Gemini Nano Banana API
        const generatedImageUrl = await callGeminiNanoBanana(completePrompt);

        // Display result
        document.getElementById('generatedImage').src = generatedImageUrl;
        document.getElementById('resultSection').style.display = 'block';

    } catch (error) {
        console.error('Error generating image:', error);
        alert('Failed to generate image. Please try again. Error: ' + error.message);
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
 * Convert file to base64 data URL
 * Converts AVIF and other formats to PNG for better compatibility
 */
async function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        // Check if file needs conversion (AVIF, WEBP, etc.)
        const needsConversion = file.type === 'image/avif' || file.type === 'image/webp';

        if (needsConversion) {
            // Convert to PNG using canvas
            const img = new Image();
            const reader = new FileReader();

            reader.onload = (e) => {
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);

                    // Convert to PNG base64
                    const pngDataUrl = canvas.toDataURL('image/png');
                    resolve(pngDataUrl);
                };
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        } else {
            // For JPEG, PNG, GIF - use directly
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        }
    });
}

/**
 * Call OpenRouter API with Google Gemini 2.5 Flash Image model (Nano Banana)
 * This model generates actual images, not just descriptions
 * Supports both image generation and image editing
 */
async function callGeminiNanoBanana(prompt) {
    console.log('Calling OpenRouter/Gemini Nano Banana API with prompt:', prompt);
    console.log('Mode:', selectedMode);
    console.log('Base image files:', baseImageFiles);
    console.log('Primary image index:', primaryImageIndex);
    console.log('Template file:', templateFile);
    console.log('Product files:', productFiles);

    try {
        // Get selected aspect ratio from dropdown
        const aspectRatioSelect = document.getElementById('aspectRatioSelect');
        let aspectRatio = aspectRatioSelect ? aspectRatioSelect.value : "1:1";

        // Build the message content array
        let contentParts = [];

        // For edit mode, include the primary base image first, then associated images
        if (selectedMode === 'edit' && baseImageFiles.length > 0) {
            // Add primary image first
            const primaryImageBase64 = await fileToBase64(baseImageFiles[primaryImageIndex]);
            contentParts.push({
                type: "image_url",
                image_url: {
                    url: primaryImageBase64
                }
            });

            // Add associated images (all images except the primary one)
            for (let i = 0; i < baseImageFiles.length; i++) {
                if (i !== primaryImageIndex) {
                    const associatedImageBase64 = await fileToBase64(baseImageFiles[i]);
                    contentParts.push({
                        type: "image_url",
                        image_url: {
                            url: associatedImageBase64
                        }
                    });
                }
            }
        }

        // For create mode with template, include template image
        if (selectedMode === 'create' && templateFile) {
            const templateBase64 = await fileToBase64(templateFile);
            contentParts.push({
                type: "image_url",
                image_url: {
                    url: templateBase64
                }
            });
        }

        // Add product images if any (for create mode)
        if (selectedMode === 'create' && productFiles.length > 0) {
            for (const productFile of productFiles) {
                const productBase64 = await fileToBase64(productFile);
                contentParts.push({
                    type: "image_url",
                    image_url: {
                        url: productBase64
                    }
                });
            }
        }

        // Add the text prompt
        contentParts.push({
            type: "text",
            text: prompt
        });

        // Prepare the API request body
        const requestBody = {
            model: "google/gemini-2.5-flash-image",
            messages: [
                {
                    role: "user",
                    content: contentParts
                }
            ],
            modalities: ["image", "text"], // Request both image and text output
            image_config: {
                aspect_ratio: aspectRatio
            }
        };

        // Get CSRF token
        const csrfTokenName = '<?= csrf_token() ?>';
        const csrfToken = document.querySelector('input[name="' + csrfTokenName + '"]').value;

        // Make API call to backend endpoint (keeps API key secure)
        const response = await fetch('<?= base_url("image-creator/generate") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                ...requestBody,
                [csrfTokenName]: csrfToken
            })
        });

        const responseData = await response.json();

        // Update CSRF token
        if (responseData.csrf_token) {
            document.querySelector('input[name="' + csrfTokenName + '"]').value = responseData.csrf_token;
        }

        if (!responseData.success) {
            throw new Error(responseData.error || 'API request failed');
        }

        const data = responseData.data;
        console.log('API Response:', data);

        // Extract the generated image from the response
        if (data.choices && data.choices[0] && data.choices[0].message) {
            const message = data.choices[0].message;

            // Check if images were generated
            if (message.images && message.images.length > 0) {
                // Return the first generated image (base64 data URL)
                const imageUrl = message.images[0].image_url.url;
                console.log('Generated image received successfully');
                return imageUrl;
            }
            // If no images but there's text content
            else if (message.content) {
                console.log('Text response:', message.content);
                throw new Error('Model returned text instead of image. Response: ' + message.content);
            }
            else {
                throw new Error('No image generated in the response');
            }
        } else {
            throw new Error('Unexpected API response format');
        }

    } catch (error) {
        console.error('Error calling OpenRouter API:', error);
        throw error;
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
