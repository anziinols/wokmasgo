<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Welcome to WOKMASGO</h1>
            <p class="hero-subtitle">Your All-in-One Management System</p>
            <p style="font-size: 1.1rem; opacity: 0.9;">Access all your applications from one central hub</p>
        </div>
    </div>
</section>

<!-- Apps Section -->
<section class="apps-section">
    <div class="container">
        <h2 class="section-title">Available Applications</h2>
        <p class="section-subtitle">Choose an application to get started</p>
        
        <div class="apps-grid">
            <?php foreach ($apps as $app): ?>
                <div class="app-card">
                    <div class="app-icon <?= $app['gradient'] ?>">
                        <i class="<?= $app['icon'] ?>"></i>
                    </div>
                    <h3 class="app-name"><?= esc($app['name']) ?></h3>
                    <p class="app-description"><?= esc($app['description']) ?></p>
                    <a href="<?= esc($app['url']) ?>" class="app-button">
                        Open App <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Quick Stats Section -->
<section class="py-5 mt-5" style="background: linear-gradient(135deg, rgba(128, 0, 0, 0.05) 0%, rgba(255, 215, 0, 0.05) 100%);">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="fas fa-users fa-3x text-maroon mb-3"></i>
                    <h3 class="text-maroon fw-bold">1,234</h3>
                    <p class="text-gray">Active Users</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="fas fa-project-diagram fa-3x text-gold mb-3"></i>
                    <h3 class="text-gold fw-bold">567</h3>
                    <p class="text-gray">Projects</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="fas fa-tasks fa-3x text-maroon mb-3"></i>
                    <h3 class="text-maroon fw-bold">8,901</h3>
                    <p class="text-gray">Tasks Completed</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="stat-item">
                    <i class="fas fa-chart-line fa-3x text-gold mb-3"></i>
                    <h3 class="text-gold fw-bold">98%</h3>
                    <p class="text-gray">Success Rate</p>
                </div>
            </div>
        </div>
    </div>
</section>

