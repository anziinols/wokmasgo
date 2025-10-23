<div class="markdown-viewer-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fab fa-markdown me-2"></i>
                    Markdown Viewer
                </h1>
                <p class="hero-description">
                    Upload a Markdown file or paste your Markdown text to preview the rendered HTML output
                </p>
            </div>
        </div>
    </section>

    <!-- Main Content Section -->
    <section class="content-section">
        <div class="container">
            <div class="row">
                <!-- Input Section -->
                <div class="col-lg-6 mb-4">
                    <div class="input-card">
                        <h2 class="section-title">
                            <i class="fas fa-edit me-2"></i>
                            Input
                        </h2>

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs mb-3" id="inputTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="text-tab" data-bs-toggle="tab" data-bs-target="#text-input" type="button" role="tab">
                                    <i class="fas fa-keyboard me-1"></i> Paste Text
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="file-tab" data-bs-toggle="tab" data-bs-target="#file-input" type="button" role="tab">
                                    <i class="fas fa-file-upload me-1"></i> Upload File
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="inputTabContent">
                            <!-- Text Input Tab -->
                            <div class="tab-pane fade show active" id="text-input" role="tabpanel">
                                <form id="textForm">
                                    <div class="mb-3">
                                        <label for="markdownText" class="form-label">
                                            <i class="fab fa-markdown me-1"></i> Markdown Text
                                        </label>
                                        <textarea 
                                            class="form-control markdown-textarea" 
                                            id="markdownText" 
                                            name="markdown_text" 
                                            rows="15" 
                                            placeholder="# Hello World&#10;&#10;This is **bold** and this is *italic*.&#10;&#10;- List item 1&#10;- List item 2&#10;&#10;[Link](https://example.com)"
                                        ></textarea>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Enter your Markdown text here. Supports standard Markdown syntax.
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-render">
                                        <i class="fas fa-eye me-2"></i>
                                        Render Preview
                                    </button>
                                </form>
                            </div>

                            <!-- File Upload Tab -->
                            <div class="tab-pane fade" id="file-input" role="tabpanel">
                                <form id="fileForm">
                                    <div class="mb-3">
                                        <label for="markdownFile" class="form-label">
                                            <i class="fas fa-file-alt me-1"></i> Markdown File
                                        </label>
                                        <input 
                                            class="form-control" 
                                            type="file" 
                                            id="markdownFile" 
                                            name="markdown_file" 
                                            accept=".md,.markdown,.txt"
                                        >
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Upload a .md, .markdown, or .txt file (max 5MB)
                                        </div>
                                    </div>
                                    <div class="file-info mb-3" id="fileInfo" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fas fa-file-alt me-2"></i>
                                            <strong>Selected file:</strong> <span id="fileName"></span>
                                            <br>
                                            <small><strong>Size:</strong> <span id="fileSize"></span></small>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-render">
                                        <i class="fas fa-eye me-2"></i>
                                        Render Preview
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div class="col-lg-6 mb-4">
                    <div class="preview-card">
                        <div class="preview-header">
                            <h2 class="section-title">
                                <i class="fas fa-eye me-2"></i>
                                Preview
                            </h2>
                            <button class="btn btn-success btn-copy" id="copyBtn" style="display: none;">
                                <i class="fas fa-copy me-2"></i>
                                Copy Rich Text
                            </button>
                        </div>

                        <!-- Loading State -->
                        <div class="loading-state" id="loadingState" style="display: none;">
                            <div class="spinner-border text-maroon" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3">Rendering Markdown...</p>
                        </div>

                        <!-- Empty State -->
                        <div class="empty-state" id="emptyState">
                            <i class="fab fa-markdown empty-icon"></i>
                            <p class="empty-text">No preview yet</p>
                            <p class="empty-subtext">Upload a file or paste Markdown text to see the preview</p>
                        </div>

                        <!-- Preview Content -->
                        <div class="preview-content" id="previewContent" style="display: none;">
                            <div class="markdown-output" id="markdownOutput"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade" id="successAlert" role="alert" style="display: none;">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="successMessage"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <div class="alert alert-danger alert-dismissible fade" id="errorAlert" role="alert" style="display: none;">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span id="errorMessage"></span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>

            <!-- Quick Guide Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="guide-card">
                        <h3 class="guide-title">
                            <i class="fas fa-book me-2"></i>
                            Quick Markdown Guide
                        </h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="guide-list">
                                    <li><code># Heading 1</code> - Main heading</li>
                                    <li><code>## Heading 2</code> - Subheading</li>
                                    <li><code>**bold**</code> - Bold text</li>
                                    <li><code>*italic*</code> - Italic text</li>
                                    <li><code>[Link](url)</code> - Hyperlink</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="guide-list">
                                    <li><code>- Item</code> - Unordered list</li>
                                    <li><code>1. Item</code> - Ordered list</li>
                                    <li><code>`code`</code> - Inline code</li>
                                    <li><code>```code```</code> - Code block</li>
                                    <li><code>> Quote</code> - Blockquote</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

