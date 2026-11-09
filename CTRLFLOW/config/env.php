<?php
// Fill in your own SMTP sender before deploying. Do not commit real credentials.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your_sender_email@gmail.com');
define('SMTP_PASS', 'your_app_password');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_FROM_EMAIL', SMTP_USER);
define('APP_NAME', 'Ctrl+Flow');
define('BASE_URL', 'http://localhost/CTRLFLOW/public');
?>
