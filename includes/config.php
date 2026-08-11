<?php
define('DB_PATH', '/var/lib/coolingsystems/cooling.db');
define('UPLOAD_DIR', '/var/lib/coolingsystems/uploads');
define('SESSION_LIFETIME', 86400);
define('COMMISSION_RATE', 5);
define('COD_MAX', 10000000);
define('WITHDRAWAL_MIN', 100000);
define('WITHDRAWAL_MAX', 500000000);
define('ORDER_TIMEOUT_MIN', 30);
define('AUTO_COMPLETE_DAYS', 7);
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
