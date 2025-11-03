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
    border: 3px dashed #d0d0d0;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    background: #f9f9f9;
    transition: all 0.3s ease;
}

.upload-area:hover {
    border-color: var(--gold);
    background: rgba(255, 215, 0, 0.05);
}

.upload-placeholder {
    cursor: pointer;
    padding: 1rem;
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

/* Product Previews */
.product-previews {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.product-preview-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.product-preview-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.product-preview-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 30px;
    height: 30px;
    font-size: 0.8rem;
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

    .product-previews {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }

    .btn-gradient {
        padding: 0.6rem 1.5rem;
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
