<!-- Image Creator - Logo Options Section -->
<section class="image-creator-section">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="page-title">
                <i class="fas fa-trademark me-2"></i>
                Logo Creator
            </h1>
            <p class="page-subtitle">Create professional logos for your brand with AI</p>
        </div>

        <!-- Back to Image Creator -->
        <div class="mb-4">
            <a href="<?= base_url('image-creator') ?>" class="btn btn-outline-maroon">
                <i class="fas fa-arrow-left me-2"></i>
                Back to Image Creator
            </a>
        </div>

        <!-- Logo Options -->
        <div class="card shadow-lg mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4">
                    <i class="fas fa-layer-group me-2"></i>
                    Select Logo Option
                </h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="<?= base_url('image-creator/logo/create') ?>" class="text-decoration-none">
                            <div class="image-type-card">
                                <div class="type-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h4>Create New Logo</h4>
                                <p>Generate a brand new logo from scratch</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="<?= base_url('image-creator/logo/edit') ?>" class="text-decoration-none">
                            <div class="image-type-card">
                                <div class="type-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h4>Edit Existing Logo</h4>
                                <p>Modify an existing logo image</p>
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
                    Tips for Better Logo Results
                </h5>
                <ul class="mb-0">
                    <li>Be specific about the style you want (modern, vintage, minimalist, etc.)</li>
                    <li>Mention preferred colors and color schemes</li>
                    <li>Include your brand name and industry</li>
                    <li>Describe the mood or feeling you want to convey</li>
                    <li>For editing: Upload your existing logo and describe what you want to change</li>
                </ul>
            </div>
        </div>
    </div>
</section>

