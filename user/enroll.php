<?php
require_once __DIR__.'/../config/auth.php';
require_login();
http_response_code(403);
exit('Student enrollment is disabled. Sign in only to view published lessons.');
