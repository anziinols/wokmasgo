<!-- Image Creator - Create New Logo Section -->
<section class="image-creator-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="page-title">
                <i class="fas fa-plus-circle me-2"></i>
                Create New Logo
            </h1>
            <p class="page-subtitle">Generate a professional logo for your brand with AI</p>
        </div>

        <!-- Back to Logo Options -->
        <div class="mb-4">
            <a href="<?= base_url('image-creator/logo') ?>" class="btn btn-outline-maroon">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Logo Options
            </a>
        </div>

        <!-- Logo Generation Form -->
        <div class="card shadow-lg">
            <div class="card-body">
                <!-- CSRF Token for API calls -->
                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                <input type="hidden" id="imageType" value="logo">
                <input type="hidden" id="imageMode" value="create">

                <h3 class="card-title mb-4">
                    <i class="fas fa-magic me-2"></i>
                    Generate Logo
                </h3>

                <!-- Aspect Ratio Selection -->
                <div class="mb-4">
                    <label for="aspectRatioSelect" class="form-label fw-bold">
                        <i class="fas fa-expand-arrows-alt me-2"></i>
                        Logo Aspect Ratio
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
                        Square (1:1) is recommended for logos.
                    </div>
                </div>

                <!-- Prompt Input -->
                <div class="mb-4">
                    <label for="promptInput" class="form-label fw-bold">
                        <i class="fas fa-pencil-alt me-2"></i>
                        Describe Your Logo
                    </label>
                    <textarea
                        class="form-control"
                        id="promptInput"
                        rows="4"
                        placeholder="Describe the logo you want to create... (e.g., 'Create a modern minimalist logo for a tech startup called TechFlow with blue and silver colors')"
                    ></textarea>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Be specific about your brand name, industry, colors, and style preferences.
                    </div>
                </div>

                <!-- Generation Button -->
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-lg btn-gradient" id="generateBtn" onclick="generateImage()">
                        <i class="fas fa-magic me-2"></i>
                        Generate Logo
                    </button>
                </div>

                <!-- Loading Indicator -->
                <div class="loading-container" id="loadingContainer" style="display: none;">
                    <div class="spinner-border text-maroon" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Creating your logo with AI...</p>
                </div>

                <!-- Result Section -->
                <div id="resultSection" style="display: none;">
                    <hr class="my-4">
                    <h4 class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        Generated Logo
                    </h4>
                    <div class="result-container">
                        <img id="generatedImage" src="" alt="Generated Logo" class="img-fluid rounded shadow">
                    </div>
                    <div class="mt-3 text-center">
                        <button type="button" class="btn btn-success me-2" onclick="downloadImage()">
                            <i class="fas fa-download me-2"></i>
                            Download Logo
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
                    Logo Creation Tips
                </h5>
                <ul class="mb-0">
                    <li>Include your brand or company name in the description</li>
                    <li>Specify your industry or business type</li>
                    <li>Mention preferred colors and color combinations</li>
                    <li>Describe the style (modern, vintage, minimalist, playful, professional, etc.)</li>
                    <li>Include any symbols or elements you want incorporated</li>
                    <li>Example: "Create a modern minimalist logo for TechFlow, a software company, using blue and silver colors with a flowing wave symbol"</li>
                </ul>
            </div>
        </div>
    </div>
</section>

