<!-- Image Creator Section -->
<section class="image-creator-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="page-title">
                <i class="fas fa-image me-2"></i>
                Image Creator
            </h1>
            <p class="page-subtitle">Create stunning logos and advertisement flyers with AI</p>
        </div>

        <!-- Image Type Selection -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4">
                    <i class="fas fa-layer-group me-2"></i>
                    Select Image Type
                </h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="image-type-card" id="logoTypeCard" onclick="selectImageType('logo')">
                            <div class="type-icon">
                                <i class="fas fa-trademark"></i>
                            </div>
                            <h4>Logo</h4>
                            <p>Create professional logos for your brand</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="image-type-card" id="flyerTypeCard" onclick="selectImageType('flyer')">
                            <div class="type-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h4>Advertisement Flyer</h4>
                            <p>Design eye-catching advertisement flyers</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Generation Form -->
        <div class="card shadow-lg" id="generationForm" style="display: none;">
            <div class="card-body">
                <!-- CSRF Token for API calls -->
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">

                <h3 class="card-title mb-4">
                    <i class="fas fa-magic me-2"></i>
                    Generate <span id="selectedTypeName">Image</span>
                </h3>

                <!-- Flyer Mode Selection (only for flyer type) -->
                <div class="mb-4" id="flyerModeSection" style="display: none;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-cog me-2"></i>
                        Flyer Mode
                    </label>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="mode-card" id="createModeCard" onclick="selectFlyerMode('create')">
                                <div class="mode-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h5>Create New</h5>
                                <p>Generate a new flyer from scratch</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="mode-card" id="editModeCard" onclick="selectFlyerMode('edit')">
                                <div class="mode-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h5>Edit Existing</h5>
                                <p>Edit an existing flyer image</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Base Image Upload (for edit mode) -->
                <div class="mb-4" id="baseImageSection" style="display: none;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-file-upload me-2"></i>
                        Upload Images to Edit <span class="text-danger">*</span>
                    </label>
                    <div class="form-text mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Upload multiple images. Click the star <i class="fas fa-star text-warning"></i> to set the primary image to edit. Other images can be inserted into the primary image.
                    </div>
                    <div class="upload-area" id="baseImageUploadArea">
                        <input type="file" id="baseImageInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif" multiple class="d-none">
                        <div class="upload-placeholder" onclick="document.getElementById('baseImageInput').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <p class="mb-0">Click to upload images or drag and drop</p>
                            <small class="text-muted">Supports: JPG, PNG, GIF, WEBP, AVIF (Max 5MB each, Multiple files)</small>
                        </div>
                        <div class="base-image-previews" id="baseImagePreviews"></div>
                    </div>
                </div>

                <!-- Flyer Template Upload (only for create mode) -->
                <div class="mb-4" id="templateUploadSection" style="display: none;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-file-upload me-2"></i>
                        Upload Flyer Template (Optional)
                    </label>
                    <div class="upload-area" id="templateUploadArea">
                        <input type="file" id="templateInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif" class="d-none">
                        <div class="upload-placeholder" onclick="document.getElementById('templateInput').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <p class="mb-0">Click to upload template or drag and drop</p>
                            <small class="text-muted">Supports: JPG, PNG, GIF, WEBP, AVIF (Max 5MB)</small>
                        </div>
                        <div class="template-preview" id="templatePreview" style="display: none;">
                            <img id="templatePreviewImg" src="" alt="Template Preview">
                            <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeTemplate()">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Images Upload (only for flyer type) -->
                <div class="mb-4" id="productImagesSection" style="display: none;">
                    <label class="form-label fw-bold">
                        <i class="fas fa-images me-2"></i>
                        Upload Product Images (Multiple)
                    </label>
                    <div class="upload-area" id="productUploadArea">
                        <input type="file" id="productImagesInput" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/avif" multiple class="d-none">
                        <div class="upload-placeholder" onclick="document.getElementById('productImagesInput').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <p class="mb-0">Click to upload product images or drag and drop</p>
                            <small class="text-muted">Supports: JPG, PNG, GIF, WEBP, AVIF (Max 5MB each)</small>
                        </div>
                        <div class="product-previews" id="productPreviews"></div>
                    </div>
                </div>

                <!-- Aspect Ratio Selection -->
                <div class="mb-4">
                    <label for="aspectRatioSelect" class="form-label fw-bold">
                        <i class="fas fa-expand-arrows-alt me-2"></i>
                        Image Aspect Ratio
                    </label>
                    <select class="form-select" id="aspectRatioSelect">
                        <option value="1:1">Square (1:1) - 1024×1024</option>
                        <option value="2:3">Portrait 2:3 - 832×1248</option>
                        <option value="3:2">Landscape 3:2 - 1248×832</option>
                        <option value="3:4">Portrait 3:4 - 864×1184</option>
                        <option value="4:3">Landscape 4:3 - 1184×864</option>
                        <option value="4:5">Portrait 4:5 - 896×1152</option>
                        <option value="5:4">Landscape 5:4 - 1152×896</option>
                        <option value="9:16">Vertical 9:16 - 768×1344</option>
                        <option value="16:9">Widescreen 16:9 - 1344×768</option>
                        <option value="21:9">Ultra-wide 21:9 - 1536×672</option>
                    </select>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Choose the aspect ratio that best fits your needs.
                    </div>
                </div>

                <!-- Prompt Input -->
                <div class="mb-4">
                    <label for="promptInput" class="form-label fw-bold">
                        <i class="fas fa-pencil-alt me-2"></i>
                        <span id="promptLabel">Enter Your Prompt</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="promptInput"
                        rows="4"
                        placeholder="Describe the image you want to create... (e.g., 'Create a modern minimalist logo for a tech startup called TechFlow with blue and silver colors')"
                    ></textarea>
                    <div class="form-text" id="promptHelpText">
                        <i class="fas fa-info-circle me-1"></i>
                        Be specific about colors, style, and elements you want in your image.
                    </div>
                </div>

                <!-- Generation Button -->
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-lg btn-gradient" id="generateBtn" onclick="generateImage()">
                        <i class="fas fa-magic me-2"></i>
                        Generate Image
                    </button>
                </div>

                <!-- Loading Indicator -->
                <div class="loading-container" id="loadingContainer" style="display: none;">
                    <div class="spinner-border text-maroon" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Creating your image with AI...</p>
                </div>

                <!-- Result Section -->
                <div id="resultSection" style="display: none;">
                    <hr class="my-4">
                    <h4 class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Generated Image
                    </h4>
                    <div class="result-container">
                        <img id="generatedImage" src="" alt="Generated Image" class="img-fluid rounded shadow">
                    </div>
                    <div class="mt-3 text-center">
                        <button type="button" class="btn btn-success me-2" onclick="downloadImage()">
                            <i class="fas fa-download me-2"></i>
                            Download Image
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-redo me-2"></i>
                            Create Another
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="card shadow-sm mt-4 bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-question-circle me-2"></i>
                    Tips for Better Results
                </h5>
                <ul class="mb-0">
                    <li>Be specific about the style you want (modern, vintage, minimalist, etc.)</li>
                    <li>Mention preferred colors and color schemes</li>
                    <li>For logos: Include your brand name and industry</li>
                    <li>For flyers: Describe the product, promotion, and target audience</li>
                    <li>Upload high-quality product images for better flyer results</li>
                    <li><strong>Image Editing:</strong> Upload an existing image and describe what you want to change (e.g., "Change the blue sofa to brown leather", "Add a logo to the shirt")</li>
                </ul>
            </div>
        </div>
    </div>
</section>
