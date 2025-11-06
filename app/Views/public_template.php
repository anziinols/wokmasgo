<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $page_title ?? 'WOKMASGO' ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= base_url('assets/images/wokmasgo_favicon.ico') ?>">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --maroon: #800000;
            --maroon-dark: #5c0000;
            --maroon-light: #a52a2a;
            --gold: #FFD700;
            --gold-dark: #DAA520;
            --gold-light: #FFEC8B;
            --black: #000000;
            --white: #FFFFFF;
            --gray-light: #f8f9fa;
            --gray: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--gray-light) 0%, var(--white) 100%);
            min-height: 100vh;
        }
        
        /* Navbar Styles */
        .navbar {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--maroon-dark) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            color: var(--white) !important;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .navbar-brand img {
            height: 50px;
            margin-right: 15px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }
        
        .navbar-nav .nav-link {
            color: var(--white) !important;
            margin: 0 10px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--gold) !important;
            transform: translateY(-2px);
        }
        
        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--gold);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        
        .navbar-nav .nav-link:hover::after {
            width: 80%;
        }
        
        .navbar-toggler {
            border-color: var(--gold);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 215, 0, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        /* Main Content */
        .main-content {
            min-height: calc(100vh - 200px);
            padding: 2rem 0;
        }
        
        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, var(--maroon-dark) 0%, var(--black) 100%);
            color: var(--white);
            padding: 2rem 0 1rem;
            margin-top: 3rem;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
        }
        
        .footer-logo img {
            height: 40px;
            margin-right: 10px;
        }
        
        .footer-links a {
            color: var(--gold);
            text-decoration: none;
            margin: 0 15px;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--gold-light);
            text-decoration: underline;
        }
        
        .footer-social a {
            color: var(--white);
            font-size: 1.5rem;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .footer-social a:hover {
            color: var(--gold);
            transform: scale(1.2);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 215, 0, 0.3);
            font-size: 0.9rem;
        }
        
        /* Gradient Backgrounds */
        .gradient-maroon-gold {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--gold) 100%);
        }
        
        .gradient-gold-maroon {
            background: linear-gradient(135deg, var(--gold) 0%, var(--maroon) 100%);
        }
        
        .gradient-maroon-black {
            background: linear-gradient(135deg, var(--maroon) 0%, var(--black) 100%);
        }
        
        /* Utility Classes */
        .text-maroon {
            color: var(--maroon) !important;
        }
        
        .text-gold {
            color: var(--gold-dark) !important;
        }
        
        .bg-maroon {
            background-color: var(--maroon) !important;
        }
        
        .bg-gold {
            background-color: var(--gold) !important;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1.2rem;
            }
            
            .navbar-brand img {
                height: 40px;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-logo,
            .footer-links,
            .footer-social {
                margin-bottom: 1rem;
            }
        }
        
        /* Additional Page-Specific Styles */
        <?= $additional_css ?? '' ?>
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url() ?>">
                <img src="<?= base_url('assets/images/wokmasgo_logo.png') ?>" alt="WOKMASGO Logo">
                WOKMASGO
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url() ?>">Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <?= $main_content ?? '' ?>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="<?= base_url('assets/images/wokmasgo_logo.png') ?>" alt="WOKMASGO Logo">
                    <span>WOKMASGO</span>
                </div>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Help</a>
                </div>
                <div class="footer-social">
                    <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> WOKMASGO. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional Scripts -->
    <?= $additional_js ?? '' ?>
</body>
</html>

