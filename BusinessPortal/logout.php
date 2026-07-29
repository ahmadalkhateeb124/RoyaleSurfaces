<?php
declare(strict_types=1);

define('PORTAL_PUBLIC', true);
require_once __DIR__ . '/inc/bootstrap.php';

auth_logout();
session_start();
flash('ok', 'You have been signed out.');

header('Location: ' . portal_url('login.php'));
exit;
