.image-creator-section {
    padding: 2rem 0;
}

.page-title {
    color: var(--maroon);
    font-weight: 700;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.page-subtitle {
    color: var(--gray);
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

.card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s ease;
}

.card-title {
    color: var(--maroon);
    font-weight: 600;
    font-size: 1.5rem;
}

/* Image Type Cards */
.image-type-card {
    border: 3px solid #e0e0e0;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--white);
    height: 100%;
}

.image-type-card:hover {
    border-color: var(--gold);
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(128, 0, 0, 0.15);
}

.image-type-card.active {
    border-color: var(--maroon);
    background: linear-gradient(135deg, rgba(128, 0, 0, 0.05) 0%, rgba(255, 215, 0, 0.05) 100%);
}

.type-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, var(--maroon) 0%, var(--gold) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.image-type-card:hover .type-icon {
    transform: rotate(10deg) scale(1.1);
}

.type-icon i {
    font-size: 2.5rem;
    color: var(--white);
}

.image-type-card h4 {
    color: var(--maroon);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.image-type-card p {
    color: var(--gray);
    margin-bottom: 0;
    font-size: 0.95rem;
}

/* Mode Cards (for flyer mode selection) */
.mode-card {
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--white);
    height: 100%;
}

.mode-card:hover {
    border-color: var(--gold);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(128, 0, 0, 0.1);
}

.mode-card.active {
    border-color: var(--maroon);
    background: linear-gradient(135deg, rgba(128, 0, 0, 0.05) 0%, rgba(255, 215, 0, 0.05) 100%);
}

.mode-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, var(--maroon) 0%, var(--gold) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.mode-card:hover .mode-icon {
    transform: scale(1.1);
}

.mode-icon i {
    font-size: 1.8rem;
    color: var(--white);
}

.mode-card h5 {
    color: var(--maroon);
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.mode-card p {
    color: var(--gray);
    margin-bottom: 0;
    font-size: 0.9rem;
}

/* Upload Area */
.upload-area {
    position: relative;
    border: 3px dashed #d0d0d0;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    background: #f9f9f9;
    transition: all 0.3s ease;
    overflow: hidden;
}

.upload-area:hover,
.upload-area:focus-within {
    border-color: var(--gold);
    background: rgba(255, 215, 0, 0.05);
}

/* Active state for touch devices */
.upload-area:active {
    border-color: var(--maroon);
    background: rgba(128, 0, 32, 0.05);
}

.upload-placeholder {
    cursor: pointer;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 120px;
}

.upload-placeholder i {
    color: var(--maroon);
    opacity: 0.6;
}

.upload-placeholder p {
    color: var(--maroon);
    font-weight: 500;
    margin-top: 0.5rem;
}

/* Template Preview */
.template-preview {
    position: relative;
    display: inline-block;
    max-width: 100%;
}

.template-preview img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.remove-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Product Previews Container - Separate section below upload zone */
.product-previews-container {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
}

.product-previews-header {
    color: var(--maroon);
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #dee2e6;
}

/* Product Upload Area - Inline Previews */
.product-upload-area {
    min-height: 150px;
    position: relative;
}

.product-upload-area.has-images {
    padding: 15px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Inline previews container inside upload area */
.product-previews-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    width: 100%;
}

/* Product previews grid inside upload area */
.product-previews-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    flex: 1;
}

.product-preview-item {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
    background: #fff;
    flex-shrink: 0;
}

.product-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-preview-item .remove-btn {
    position: absolute;
    top: 3px;
    right: 3px;
    width: 24px;
    height: 24px;
    font-size: 0.7rem;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    opacity: 0.9;
}

.product-preview-item:hover .remove-btn {
    opacity: 1;
}

/* Add more images button */
.add-more-images-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    border: 2px dashed #667eea;
    border-radius: 10px;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.add-more-images-btn:hover {
    background: rgba(102, 126, 234, 0.2);
    border-color: #5a67d8;
    transform: scale(1.05);
}

.add-more-images-btn i {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.add-more-images-btn span {
    font-size: 0.75rem;
    font-weight: 600;
}

/* Responsive adjustments for mobile */
@media (max-width: 576px) {
    .product-preview-item,
    .add-more-images-btn {
        width: 80px;
        height: 80px;
    }

    .product-preview-item .remove-btn {
        width: 22px;
        height: 22px;
        font-size: 0.65rem;
    }

    .add-more-images-btn i {
        font-size: 1.2rem;
    }

    .add-more-images-btn span {
        font-size: 0.65rem;
    }
}

/* Base Image Previews (for edit mode with multiple images) */
.base-image-previews {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.base-image-preview-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.base-image-preview-item.primary {
    border: 3px solid var(--gold);
    box-shadow: 0 5px 20px rgba(255, 215, 0, 0.4);
}

.base-image-preview-item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.base-image-preview-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 30px;
    height: 30px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.9;
    z-index: 2;
}

.base-image-preview-item .remove-btn:hover {
    opacity: 1;
    transform: scale(1.1);
}

.base-image-preview-item .primary-star {
    position: absolute;
    top: 5px;
    left: 5px;
    width: 35px;
    height: 35px;
    background: rgba(0, 0, 0, 0.6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
}

.base-image-preview-item .primary-star:hover {
    background: rgba(0, 0, 0, 0.8);
    transform: scale(1.1);
}

.base-image-preview-item .primary-star i {
    font-size: 18px;
    color: #ccc;
    transition: color 0.3s ease;
}

.base-image-preview-item.primary .primary-star i {
    color: var(--gold);
}

.base-image-preview-item .image-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    color: white;
    padding: 8px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
}

.base-image-preview-item.primary .image-label {
    background: linear-gradient(to top, var(--maroon), transparent);
}

/* Form Controls */
.form-label {
    color: var(--maroon);
    margin-bottom: 0.75rem;
}

.form-control {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--maroon);
    box-shadow: 0 0 0 0.25rem rgba(128, 0, 0, 0.15);
}

.form-text {
    color: var(--gray);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

/* Buttons */
.btn-gradient {
    background: linear-gradient(135deg, var(--maroon) 0%, var(--gold) 100%);
    border: none;
    color: var(--white);
    padding: 0.75rem 2.5rem;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(128, 0, 0, 0.3);
}

.btn-gradient:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(128, 0, 0, 0.4);
    color: var(--white);
}

.btn-gradient:active {
    transform: translateY(-1px);
}

.btn-gradient:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Loading Container */
.loading-container {
    text-align: center;
    padding: 3rem 1rem;
}

.loading-container p {
    color: var(--maroon);
    font-weight: 500;
    font-size: 1.1rem;
}

.spinner-border {
    width: 3rem;
    height: 3rem;
    border-width: 0.3rem;
}

/* Result Container */
.result-container {
    text-align: center;
    padding: 2rem;
    background: #f9f9f9;
    border-radius: 15px;
}

.result-container img {
    max-width: 100%;
    max-height: 600px;
    border: 3px solid var(--maroon);
}

/* Mobile file input styling - ensures proper visibility and touch targets */
.mobile-file-input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Upload label styling for mobile */
.upload-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

/* Responsive */
@media (max-width: 768px) {
    .page-title {
        font-size: 2rem;
    }

    .image-type-card {
        padding: 1.5rem;
        margin-bottom: 1rem;
    }

    .type-icon {
        width: 60px;
        height: 60px;
    }

    .type-icon i {
        font-size: 2rem;
    }

    .product-previews-container {
        padding: 0.75rem;
        margin-top: 0.75rem;
    }

    .product-previews-header {
        font-size: 0.8rem;
    }

    .product-previews {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
    }

    .btn-gradient {
        padding: 0.6rem 1.5rem;
    }

    /* Mobile upload area improvements */
    .upload-area {
        min-height: 150px;
        touch-action: manipulation;
    }

    .upload-placeholder {
        padding: 1.5rem;
    }

    .upload-placeholder i {
        font-size: 2.5rem !important;
    }

    .upload-placeholder p {
        font-size: 0.9rem;
    }

    .upload-placeholder small {
        font-size: 0.75rem;
    }

    /* Mobile image preview improvements */
    .template-preview img {
        max-height: 250px;
    }

    .base-image-previews {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
    }

    .base-image-preview-item {
        min-height: 100px;
    }

    .base-image-preview-item img {
        max-height: 100px;
    }

    .product-preview-item {
        min-height: 80px;
    }

    .product-preview-item img {
        max-height: 80px;
    }

    /* Larger touch targets for buttons on mobile */
    .remove-btn {
        min-width: 36px;
        min-height: 36px;
        padding: 8px !important;
    }

    .primary-star {
        min-width: 36px;
        min-height: 36px;
        padding: 8px;
    }

    .image-label {
        font-size: 0.65rem;
        padding: 2px 4px;
    }

    /* Result section mobile adjustments */
    .result-container {
        padding: 1rem;
    }

    .result-container img {
        max-height: 400px;
    }

    /* Prompt input mobile adjustments */
    #promptInput {
        font-size: 16px; /* Prevents iOS zoom on focus */
    }
}

/* Animation */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#generationForm {
    animation: fadeIn 0.5s ease;
}

#resultSection {
    animation: fadeIn 0.5s ease;
}
