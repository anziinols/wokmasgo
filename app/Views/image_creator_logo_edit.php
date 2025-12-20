<!-- Image Creator - Edit Existing Logo Section -->
<section class="image-creator-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="page-title">
                <i class="fas fa-edit me-2"></i>
                Edit Existing Logo
            </h1>
            <p class="page-subtitle">Modify your existing logo with AI</p>
        </div>

        <!-- Back to Logo Options -->
        <div class="mb-4">
            <a href="<?= base_url('image-creator/logo') ?>" class="btn btn-outline-maroon">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Logo Options
            </a>
        </div>

        <!-- Logo Edit Form -->
        <div class="card shadow-lg">
            <div class="card-body">
                <!-- CSRF Token for API calls -->
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                <input type="hidden" id="imageType" value="logo">
                <input type="hidden" id="imageMode" value="edit">

                <h3 class="card-title mb-4">
                    <i class="fas fa-magic me-2"></i>
                    Edit Logo
                </h3>

                <!-- Base Image Upload -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-file-upload me-2"></i>
                        Upload Logo to Edit <span class="text-danger">*</span>
                    </label>
                    <div class="form-text mb-2">
                        <i class="fas fa-info-circle me-1"></i>
                        Upload your existing logo image that you want to modify.
                    </div>
                    <div class="upload-area" id="baseImageUploadArea">
                        <input type="file" id="baseImageInput" accept="image/*" class="mobile-file-input">
                        <label for="baseImageInput" class="upload-placeholder upload-label">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <p class="mb-0">Tap to upload logo or drag and drop</p>
                            <small class="text-muted">Supports: JPG, PNG, GIF, WEBP, AVIF (Max 5MB)</small>
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
                        <option value="1:1" selected>Square (1:1) - 1024×1024 - Recommended</option>
                        <option value="2:3">Portrait 2:3 - 832×1248</option>
                        <option value="3:2">Landscape 3:2 - 1248×832</option>
                        <option value="3:4">Portrait 3:4 - 864×1184</option>
                        <option value="4:3">Landscape 4:3 - 1184×864</option>
                        <option value="4:5">Portrait 4:5 - 896×1152</option>
                        <option value="5:4">Landscape 5:4 - 1152×896</option>
                    </select>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Choose the aspect ratio for the edited logo.
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
                        placeholder="Describe what you want to change in the logo... (e.g., 'Change the blue color to green', 'Add a star icon above the text', 'Make the design more modern')"
                    ></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Be specific about what you want to change, add, or remove from the logo.
                    </div>
                </div>

                <!-- Generation Button -->
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-lg btn-gradient" id="generateBtn" onclick="generateImage()">
                        <i class="fas fa-magic me-2"></i>
                        Edit Logo
                    </button>
                </div>

                <!-- Loading Indicator -->
                <div class="loading-container" id="loadingContainer" style="display: none;">
                    <div class="spinner-border text-maroon" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Editing your logo with AI...</p>
                </div>

                <!-- Result Section -->
                <div id="resultSection" style="display: none;">
                    <hr class="my-4">
                    <h4 class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Edited Logo
                    </h4>
                    <div class="result-container">
                        <img id="generatedImage" src="" alt="Edited Logo" class="img-fluid rounded shadow">
                    </div>
                    <div class="mt-3 text-center">
                        <button type="button" class="btn btn-success me-2" onclick="downloadImage()">
                            <i class="fas fa-download me-2"></i>
                            Download Logo
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
                    Logo Editing Tips
                </h5>
                <ul class="mb-0">
                    <li>Upload a clear, high-quality version of your existing logo</li>
                    <li>Be specific about what you want to change (colors, elements, style, etc.)</li>
                    <li>Example: "Change the blue color to forest green"</li>
                    <li>Example: "Add a subtle shadow effect to the text"</li>
                    <li>Example: "Replace the circle with a hexagon shape"</li>
                    <li>Example: "Make the design more minimalist by removing decorative elements"</li>
                </ul>
            </div>
        </div>
    </div>
</section>

