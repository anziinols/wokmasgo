<!-- Image Creator - Advertisement Flyer Options Section -->
<section class="image-creator-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="page-title">
                <i class="fas fa-file-alt me-2"></i>
                Advertisement Flyer Creator
            </h1>
            <p class="page-subtitle">Design eye-catching advertisement flyers with AI</p>
        </div>

        <!-- Back to Image Creator -->
        <div class="mb-4">
            <a href="<?= base_url('image-creator') ?>" class="btn btn-outline-maroon">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Image Creator
            </a>
        </div>

        <!-- Flyer Options -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4">
                    <i class="fas fa-layer-group me-2"></i>
                    Select Flyer Option
                </h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="<?= base_url('image-creator/flyer/create') ?>" class="text-decoration-none">
                            <div class="image-type-card">
                                <div class="type-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h4>Create New Flyer</h4>
                                <p>Generate a new flyer from scratch</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="<?= base_url('image-creator/flyer/edit') ?>" class="text-decoration-none">
                            <div class="image-type-card">
                                <div class="type-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h4>Edit Existing Flyer</h4>
                                <p>Modify an existing flyer image</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="card shadow-sm mt-4 bg-light">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="fas fa-question-circle me-2"></i>
                    Tips for Better Flyer Results
                </h5>
                <ul class="mb-0">
                    <li>Describe the product, promotion, and target audience</li>
                    <li>Be specific about colors, style, and elements you want</li>
                    <li>Upload high-quality product images for better results</li>
                    <li>Mention the occasion or event (sale, grand opening, etc.)</li>
                    <li>For editing: Upload your existing flyer and describe the changes needed</li>
                </ul>
            </div>
        </div>
    </div>
</section>

