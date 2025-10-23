/* Markdown Viewer Specific Styles */

.markdown-viewer-container {
    min-height: calc(100vh - 200px);
}

/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
    color: var(--white);
    padding: 3rem 0;
    margin-bottom: 2rem;
}

.hero-content {
    text-align: center;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-description {
    font-size: 1.1rem;
    opacity: 0.95;
    max-width: 700px;
    margin: 0 auto;
}

/* Content Section */
.content-section {
    padding: 2rem 0;
}

/* Input Card */
.input-card {
    background: var(--white);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    height: 100%;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--maroon);
    margin-bottom: 1.5rem;
}

/* Tabs */
.nav-tabs {
    border-bottom: 2px solid var(--gold);
}

.nav-tabs .nav-link {
    color: var(--gray);
    border: none;
    border-bottom: 3px solid transparent;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: var(--maroon);
    border-bottom-color: var(--gold-light);
}

.nav-tabs .nav-link.active {
    color: var(--maroon);
    background: transparent;
    border-bottom-color: var(--gold);
}

/* Textarea */
.markdown-textarea {
    font-family: 'Courier New', monospace;
    font-size: 0.95rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 1rem;
    transition: border-color 0.3s ease;
    resize: vertical;
}

.markdown-textarea:focus {
    border-color: var(--maroon);
    box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.1);
}

/* File Input */
.form-control[type="file"] {
    border: 2px dashed #e0e0e0;
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-control[type="file"]:hover {
    border-color: var(--maroon);
    background-color: rgba(128, 0, 0, 0.02);
}

.file-info .alert {
    border-left: 4px solid var(--gold);
}

/* Buttons */
.btn-render {
    background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
    border: none;
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(128, 0, 0, 0.2);
}

.btn-render:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(128, 0, 0, 0.3);
}

.btn-copy {
    background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold) 100%);
    border: none;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    color: var(--maroon-dark);
}

.btn-copy:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(218, 165, 32, 0.4);
    color: var(--maroon-dark);
}

/* Preview Card */
.preview-card {
    background: var(--white);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    height: 100%;
    min-height: 600px;
    display: flex;
    flex-direction: column;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

/* Loading State */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: var(--maroon);
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
}

.text-maroon {
    color: var(--maroon) !important;
    border-color: var(--maroon) !important;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: var(--gray);
    flex: 1;
}

.empty-icon {
    font-size: 5rem;
    color: var(--gray-light);
    margin-bottom: 1rem;
}

.empty-text {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--gray);
}

.empty-subtext {
    font-size: 0.95rem;
    color: var(--gray);
    opacity: 0.8;
}

/* Preview Content */
.preview-content {
    flex: 1;
    overflow-y: auto;
}

.markdown-output {
    padding: 1.5rem;
    background: var(--gray-light);
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    min-height: 400px;
}

/* Markdown Output Styling */
.markdown-output h1,
.markdown-output h2,
.markdown-output h3,
.markdown-output h4,
.markdown-output h5,
.markdown-output h6 {
    color: var(--maroon);
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.markdown-output h1 {
    font-size: 2rem;
    border-bottom: 2px solid var(--gold);
    padding-bottom: 0.5rem;
}

.markdown-output h2 {
    font-size: 1.7rem;
    border-bottom: 1px solid var(--gold-light);
    padding-bottom: 0.4rem;
}

.markdown-output p {
    margin-bottom: 1rem;
    line-height: 1.7;
}

.markdown-output a {
    color: var(--maroon);
    text-decoration: underline;
}

.markdown-output a:hover {
    color: var(--maroon-dark);
}

.markdown-output code {
    background: rgba(128, 0, 0, 0.1);
    color: var(--maroon-dark);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
}

.markdown-output pre {
    background: var(--maroon-dark);
    color: var(--white);
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    margin: 1rem 0;
}

.markdown-output pre code {
    background: transparent;
    color: var(--white);
    padding: 0;
}

.markdown-output blockquote {
    border-left: 4px solid var(--gold);
    padding-left: 1rem;
    margin: 1rem 0;
    color: var(--gray);
    font-style: italic;
}

.markdown-output ul,
.markdown-output ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.markdown-output li {
    margin-bottom: 0.5rem;
}

.markdown-output table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.markdown-output table th,
.markdown-output table td {
    border: 1px solid #ddd;
    padding: 0.75rem;
    text-align: left;
}

.markdown-output table th {
    background: var(--maroon);
    color: var(--white);
    font-weight: 600;
}

.markdown-output table tr:nth-child(even) {
    background: var(--gray-light);
}

/* Guide Card */
.guide-card {
    background: linear-gradient(135deg, rgba(128, 0, 0, 0.05) 0%, rgba(255, 215, 0, 0.05) 100%);
    border-radius: 12px;
    padding: 2rem;
    border: 1px solid rgba(128, 0, 0, 0.1);
}

.guide-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--maroon);
    margin-bottom: 1.5rem;
}

.guide-list {
    list-style: none;
    padding: 0;
}

.guide-list li {
    padding: 0.5rem 0;
    color: var(--black);
}

.guide-list code {
    background: var(--white);
    color: var(--maroon);
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    border: 1px solid rgba(128, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 991px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .preview-card {
        min-height: 400px;
    }
}

@media (max-width: 767px) {
    .hero-title {
        font-size: 1.7rem;
    }
    
    .hero-description {
        font-size: 1rem;
    }
    
    .input-card,
    .preview-card {
        padding: 1.5rem;
    }
    
    .preview-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}

