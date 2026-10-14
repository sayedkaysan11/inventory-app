<?php
require_once __DIR__ . '/../helpers/auth.php';
header('Location: ' . base_url(current_user() ? 'dashboard.php' : 'login.php'));
