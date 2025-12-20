<!-- Image Creator - Edit Existing Flyer Section -->
<section class="image-creator-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="page-title">
                <i class="fas fa-edit me-2"></i>
                Edit Existing Advertisement Flyer
            </h1>
            <p class="page-subtitle">Modify your existing flyer with AI</p>
        </div>

        <!-- Back to Flyer Options -->
        <div class="mb-4">
            <a href="<?= base_url('image-creator/flyer') ?>" class="btn btn-outline-maroon">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Flyer Options
            </a>
        </div>

        <!-- Flyer Edit Form -->
        <div class="card shadow-lg">
            <div class="card-body">
                <!-- CSRF Token for API calls -->
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                <input type="hidden" id="imageType" value="flyer">
                <input type="hidden" id="imageMode" value="edit">

                <h3 class="card-title mb-4">
                    <i class="fas fa-magic me-2"></i>
                    Edit Flyer
                </h3>

                <!-- Base Image Upload -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-file-upload me-2"></i>
                        Upload Images to Edit <span class="text-danger">*</span>
                    </label>
                    <div class="form-text mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Upload multiple images. Click the star <i class="fas fa-star text-warning"></i> to set the primary image to edit. Other images can be inserted into the primary image.
                    </div>
                    <div class="upload-area" id="baseImageUploadArea">
                        <input type="file" id="baseImageInput" accept="image/*" multiple class="mobile-file-input">
                        <label for="baseImageInput" class="upload-placeholder upload-label">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <p class="mb-0">Tap to upload images or drag and drop</p>
                            <small class="text-muted">Supports: JPG, PNG, GIF, WEBP, AVIF (Max 5MB each, Multiple files)</small>
                        </label>
                        <div class="base-image-previews" id="baseImagePreviews"></div>
                    </div>
                </div>

                <!-- Aspect Ratio Selection -->
                <div class="mb-4">
                    <label for="aspectRatioSelect" class="form-label fw-bold">
                        <i class="fas fa-expand-arrows-alt me-2"></i>
                        Output Aspect Ratio
                    </label>
                    <select class="form-select" id="aspectRatioSelect">
                        <option value="2:3">Portrait 2:3 - 832×1248 - Recommended</option>
                        <option value="3:4">Portrait 3:4 - 864×1184</option>
                        <option value="4:5">Portrait 4:5 - 896×1152</option>
                        <option value="9:16">Vertical 9:16 - 768×1344</option>
                        <option value="1:1">Square (1:1) - 1024×1024</option>
                        <option value="3:2">Landscape 3:2 - 1248×832</option>
                        <option value="4:3">Landscape 4:3 - 1184×864</option>
                        <option value="16:9">Widescreen 16:9 - 1344×768</option>
                    </select>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Choose the aspect ratio for the edited flyer.
                    </div>
                </div>

                <!-- Prompt Input -->
                <div class="mb-4">
                    <label for="promptInput" class="form-label fw-bold">
                        <i class="fas fa-pencil-alt me-2"></i>
                        Describe Your Changes
                    </label>
                    <textarea
                        class="form-control"
                        id="promptInput"
                        rows="4"
                        placeholder="Describe what you want to change in the flyer... (e.g., 'Change the discount from 30% to 50%', 'Replace the product image with the uploaded one', 'Change background to blue')"
                    ></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Be specific about what you want to change, add, or remove from the flyer.
                    </div>
                </div>

                <!-- Generation Button -->
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-lg btn-gradient" id="generateBtn" onclick="generateImage()">
                        <i class="fas fa-magic me-2"></i>
                        Edit Flyer
                    </button>
                </div>

                <!-- Loading Indicator -->
                <div class="loading-container" id="loadingContainer" style="display: none;">
                    <div class="spinner-border text-maroon" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Editing your flyer with AI...</p>
                </div>

                <!-- Result Section -->
                <div id="resultSection" style="display: none;">
                    <hr class="my-4">
                    <h4 class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Edited Flyer
                    </h4>
                    <div class="result-container">
                        <img id="generatedImage" src="" alt="Edited Flyer" class="img-fluid rounded shadow">
                    </div>
                    <div class="mt-3 text-center">
                        <button type="button" class="btn btn-success me-2" onclick="downloadImage()">
                            <i class="fas fa-download me-2"></i>
                            Download Flyer
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-redo me-2"></i>
                            Edit Another
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="card shadow-sm mt-4 bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-lightbulb me-2"></i>
                    Flyer Editing Tips
                </h5>
                <ul class="mb-0">
                    <li>Upload a clear, high-quality version of your existing flyer as the primary image</li>
                    <li>Upload additional product images if you want to replace or add products</li>
                    <li>Be specific about what you want to change (text, colors, images, layout, etc.)</li>
                    <li>Example: "Change the discount percentage from 30% to 50%"</li>
                    <li>Example: "Replace the main product image with the uploaded image"</li>
                    <li>Example: "Change the background color to vibrant orange"</li>
                    <li>Example: "Add 'Limited Time Offer' text at the top"</li>
                </ul>
            </div>
        </div>
    </div>
</section>

