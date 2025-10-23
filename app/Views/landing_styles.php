/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 50%, var(--black) 100%);
    color: var(--white);
    padding: 4rem 0;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23FFD700" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,138.7C960,139,1056,117,1152,101.3C1248,85,1344,75,1392,69.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
    opacity: 0.3;
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
}

.hero-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.hero-subtitle {
    font-size: 1.3rem;
    font-weight: 300;
    color: var(--gold-light);
    margin-bottom: 2rem;
}

/* Apps Grid Section */
.apps-section {
    padding: 2rem 0;
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--maroon);
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
    width: 100%;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, var(--maroon) 0%, var(--gold) 100%);
    border-radius: 2px;
}

.section-subtitle {
    text-align: center;
    font-size: 1.1rem;
    color: var(--gray);
    margin-bottom: 3rem;
}

/* App Cards */
.apps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.app-card {
    background: var(--white);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.4s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
}

.app-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--maroon) 0%, var(--gold) 100%);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.app-card:hover::before {
    transform: scaleX(1);
}

.app-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(128, 0, 0, 0.2);
    border-color: var(--gold);
}

.app-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 2.5rem;
    color: var(--white);
    transition: all 0.4s ease;
    position: relative;
}

.app-card:hover .app-icon {
    transform: scale(1.1) rotate(5deg);
}

.app-name {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--maroon);
    margin-bottom: 0.8rem;
}

.app-description {
    font-size: 0.95rem;
    color: var(--gray);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.app-button {
    display: inline-block;
    padding: 0.7rem 2rem;
    background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
    color: var(--white);
    text-decoration: none;
    border-radius: 25px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.app-button:hover {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    color: var(--maroon);
    border-color: var(--maroon);
    transform: scale(1.05);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .apps-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    .apps-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1025px) {
    .apps-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

