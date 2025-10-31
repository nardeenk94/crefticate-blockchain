<?php
/**
 * Smart Base Path Detection
 * يحسب المسار الصحيح حسب المجلد الحالي
 */
$current_file = $_SERVER['PHP_SELF'];
$base_path = '';

// إذا كنا في مجلد فرعي (admin/, issuer/, requester/)
if (strpos($current_file, '/admin/') !== false ||
    strpos($current_file, '/issuer/') !== false ||
    strpos($current_file, '/requester/') !== false ||
    strpos($current_file, '/api/') !== false) {
    $base_path = '../';
} else {
    $base_path = '';
}
?>
<header class="site-header">
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="<?php echo $base_path; ?>index.php">
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>
            </div>

            <button class="navbar-toggle" id="navbarToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                <li><a href="<?php echo $base_path; ?>verify.php">Verify Certificate</a></li>

                <?php if (is_logged_in()): ?>
                    <li><a href="<?php echo $base_path; ?>dashboard.php">Dashboard</a></li>

                    <?php if (in_array($_SESSION['user_role'], ['issuer', 'admin'])): ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">Certificates</a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo $base_path; ?>issuer/issue-certificate.php">Issue Certificate</a></li>
                                <li><a href="<?php echo $base_path; ?>issuer/my-certificates.php">My Certificates</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">Admin</a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo $base_path; ?>admin/dashboard.php">Admin Dashboard</a></li>
                                <li><a href="<?php echo $base_path; ?>admin/manage-users.php">Manage Users</a></li>
                                <li><a href="<?php echo $base_path; ?>admin/manage-certificates.php">Manage Certificates</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?php echo $base_path; ?>profile.php">My Profile</a></li>
                            <li><a href="<?php echo $base_path; ?>logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo $base_path; ?>login.php">Login</a></li>
                    <li><a href="<?php echo $base_path; ?>register.php" class="btn btn-primary">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
</header>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('navbarToggle');
        const menu = document.getElementById('navbarMenu');

        if (toggle && menu) {
            toggle.addEventListener('click', function() {
                menu.classList.toggle('active');
            });
        }
    });
</script>