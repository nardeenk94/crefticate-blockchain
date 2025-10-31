<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

start_secure_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <!-- Hero Section -->
        <section class="hero">
            <h1>Welcome to Certificate Verification Platform</h1>
            <p class="lead">Secure, Blockchain-Based Certificate Authentication System</p>
            <div class="hero-buttons">
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary">Get Started</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                <?php endif; ?>
                <a href="verify.php" class="btn btn-outline">Verify Certificate</a>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <h2>Platform Features</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Secure Authentication</h3>
                    <p>Advanced security measures to protect your certificates</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">⛓️</div>
                    <h3>Blockchain Integration</h3>
                    <p>Certificates stored on blockchain for immutability</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📄</div>
                    <h3>Digital Certificates</h3>
                    <p>Store and manage electronic copies of certificates</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">✓</div>
                    <h3>Instant Verification</h3>
                    <p>Quick and reliable certificate authentication</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🏢</div>
                    <h3>Multi-Entity Support</h3>
                    <p>Support for issuers, requesters, and individuals</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Analytics Dashboard</h3>
                    <p>Comprehensive platform management tools</p>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="how-it-works">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Register</h3>
                    <p>Create an account as an issuer, requester, or individual</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Issue Certificate</h3>
                    <p>Authorized entities can issue digital certificates</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Blockchain Storage</h3>
                    <p>Certificates are stored on blockchain for security</p>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Verify Anytime</h3>
                    <p>Anyone can verify certificate authenticity instantly</p>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="statistics">
            <h2>Platform Statistics</h2>
            <div class="stats-grid">
                <?php
                $stats = get_platform_stats();
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_certificates']; ?></div>
                    <div class="stat-label">Total Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['verified_certificates']; ?></div>
                    <div class="stat-label">Verified Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Registered Users</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pending_certificates']; ?></div>
                    <div class="stat-label">Pending Verification</div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>