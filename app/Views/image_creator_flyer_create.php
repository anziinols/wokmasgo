<!-- Image Creator - Create New Flyer Section -->
<section class="image-creator-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="page-title">
                <i class="fas fa-plus-circle me-2"></i>
                Create New Advertisement Flyer
            </h1>
            <p class="page-subtitle">Generate an eye-catching flyer for your promotion with AI</p>
        </div>

        <!-- Back to Flyer Options -->
        <div class="mb-4">
            <a href="<?= base_url('image-creator/flyer') ?>" class="btn btn-outline-maroon">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Flyer Options
            </a>
        </div>

        <!-- Flyer Generation Form -->
        <div class="card shadow-lg">
            <div class="card-body">
                <!-- CSRF Token for API calls -->
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                <input type="hidden" id="imageType" value="flyer">
                <input type="hidden" id="imageMode" value="create">

                <h3 class="card-title mb-4">
                    <i class="fas fa-magic me-2"></i>
                    Generate Flyer
                </h3>

                <!-- Flyer Template Upload (Optional) -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-file-upload me-2"></i>
                        Upload Flyer Template (Optional)
                    </label>
                    <div class="upload-area" id="templateUploadArea">
                        <input type="file" id="templateInput" accept="image/*" class="mobile-file-input">
                        <label for="templateInput" class="upload-placeholder upload-label" id="templateUploadLabel">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <p class="mb-0">Tap to upload template or drag and drop</p>
                            <small class="text-muted">Supports: JPG, PNG, GIF, WEBP, AVIF (Max 5MB)</small>
                        </label>
                        <div class="template-preview" id="templatePreview" style="display: none;">
                            <img id="templatePreviewImg" src="" alt="Template Preview">
                            <button type="button" class="btn btn-sm btn-danger remove-btn" onclick="removeTemplate()">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Images Upload -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-images me-2"></i>
                        Upload Product Images (Multiple)
                        <span id="productCount" class="badge bg-primary ms-2" style="display: none;">0</span>
                    </label>
                    <div class="upload-area product-upload-area" id="productUploadArea">
                        <input type="file" id="productImagesInput" accept="image/*" multiple class="mobile-file-input">
                        <!-- Upload placeholder - shown when no images -->
                        <label for="productImagesInput" class="upload-placeholder upload-label" id="productUploadPlaceholder">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <p class="mb-0">Tap to upload product images or drag and drop</p>
                            <small class="text-muted">Supports: JPG, PNG, GIF, WEBP, AVIF (Max 5MB each)</small>
                        </label>
                        <!-- Product previews grid - shown when images are uploaded -->
                        <div class="product-previews-inline" id="productPreviewsInline" style="display: none;">
                            <div class="product-previews-grid" id="productPreviews"></div>
                            <!-- Add more images button -->
                            <label for="productImagesInput" class="add-more-images-btn" id="addMoreImagesBtn">
                                <i class="fas fa-plus"></i>
                                <span>Add More</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Aspect Ratio Selection -->
                <div class="mb-4">
                    <label for="aspectRatioSelect" class="form-label fw-bold">
                        <i class="fas fa-expand-arrows-alt me-2"></i>
                        Flyer Aspect Ratio
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
                        Portrait orientations are recommended for flyers.
                    </div>
                </div>

                <!-- Prompt Input -->
                <div class="mb-4">
                    <label for="promptInput" class="form-label fw-bold">
                        <i class="fas fa-pencil-alt me-2"></i>
                        Describe Your Flyer
                    </label>
                    <textarea
                        class="form-control"
                        id="promptInput"
                        rows="4"
                        placeholder="Describe the flyer you want to create... (e.g., 'Create a summer sale flyer with bright colors and beach theme for 50% off promotion')"
                    ></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Be specific about colors, style, promotion details, and elements you want in your flyer.
                    </div>
                </div>

                <!-- Generation Button -->
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-lg btn-gradient" id="generateBtn" onclick="generateImage()">
                        <i class="fas fa-magic me-2"></i>
                        Generate Flyer
                    </button>
                </div>

                <!-- Loading Indicator -->
                <div class="loading-container" id="loadingContainer" style="display: none;">
                    <div class="spinner-border text-maroon" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Creating your flyer with AI...</p>
                </div>

                <!-- Result Section -->
                <div id="resultSection" style="display: none;">
                    <hr class="my-4">
                    <h4 class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Generated Flyer
                    </h4>
                    <div class="result-container">
                        <img id="generatedImage" src="" alt="Generated Flyer" class="img-fluid rounded shadow">
                    </div>
                    <div class="mt-3 text-center">
                        <button type="button" class="btn btn-success me-2" onclick="downloadImage()">
                            <i class="fas fa-download me-2"></i>
                            Download Flyer
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
                    <i class="fas fa-lightbulb me-2"></i>
                    Flyer Creation Tips
                </h5>
                <ul class="mb-0">
                    <li>Upload high-quality product images for better results</li>
                    <li>Describe the promotion or event clearly (sale, grand opening, special offer, etc.)</li>
                    <li>Mention your target audience and the mood you want to convey</li>
                    <li>Specify colors and style preferences (vibrant, elegant, modern, etc.)</li>
                    <li>Include key information like discount percentages or event dates in your description</li>
                    <li>Example: "Create a vibrant summer sale flyer with 50% off, featuring beach theme and bright tropical colors"</li>
                </ul>
            </div>
        </div>
    </div>
</section>

