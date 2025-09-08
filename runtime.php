<?php
/*
 * This file FORCES Render to detect this as a PHP project
 * It must be in the root directory and named runtime.txt or index.php
 */

// Force PHP runtime detection
if (php_sapi_name() === 'cli') {
    echo "Maya Pets Backend - PHP/Laravel Application\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server API: " . php_sapi_name() . "\n";
    echo "This is a PHP application, not Node.js!\n";
} else {
    // This should redirect to Laravel's public/index.php in production
    header('Location: /public/index.php');
    exit;
}
?>
