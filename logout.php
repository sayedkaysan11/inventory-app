<?php
require_once __DIR__ . '/../helpers/auth.php';
$_SESSION = [];
session_destroy();
header('Location: ' . base_url('login.php'));
