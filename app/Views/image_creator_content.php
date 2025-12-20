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
                        <a href="<?= base_url('image-creator/logo') ?>" class="text-decoration-none">
                            <div class="image-type-card" id="logoTypeCard">
                                <div class="type-icon">
                                    <i class="fas fa-trademark"></i>
                                </div>
                                <h4>Logo</h4>
                                <p>Create professional logos for your brand</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="<?= base_url('image-creator/flyer') ?>" class="text-decoration-none">
                            <div class="image-type-card" id="flyerTypeCard">
                                <div class="type-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h4>Advertisement Flyer</h4>
                                <p>Design eye-catching advertisement flyers</p>
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
