<?php
// General Configuration
define('SITE_NAME', 'Certificate Verification Platform');
define('SITE_URL', 'http://certificate.test/certificate-platform');
define('ADMIN_EMAIL', 'admin@platform.com');

// Security
define('SESSION_LIFETIME', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);

// Upload settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/certificates/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);

// Blockchain settings (for Phase IX)
define('WEB3_PROVIDER', 'https://sepolia.infura.io/v3/YOUR_INFURA_KEY');
define('CONTRACT_ADDRESS', '0x0000000000000000000000000000000000000000');

// IPFS settings
define('IPFS_GATEWAY', 'https://ipfs.infura.io:5001');
define('IPFS_PROJECT_ID', 'YOUR_PROJECT_ID');
define('IPFS_PROJECT_SECRET', 'YOUR_PROJECT_SECRET');

// Fee settings
define('VERIFICATION_FEE', 10.00); // USD
define('CERTIFICATE_FEE', 5.00); // USD

// Timezone
date_default_timezone_set('Africa/Cairo');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
?>