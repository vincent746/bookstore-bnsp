<?php

if (defined('USER_INIT')) {
    return;
}

define('USER_INIT', true);

$root_path = dirname(__DIR__, 2);
$doc_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$root_normalized = str_replace('\\', '/', $root_path);
$base_url = str_replace($doc_root, '', $root_normalized);
$base_url = '/' . trim($base_url, '/') . '/';

define('ROOT_PATH', $root_path);
define('BASE_URL', $base_url);
define('HOME_URL', BASE_URL);
define('USER_ASSETS_URL', BASE_URL . 'assets/users/');
define('USER_INCLUDES', __DIR__ . '/');
define('USER_PROSES_URL', BASE_URL . 'users/proses.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ROOT_PATH . '/function/users/auth.php';
require_once ROOT_PATH . '/function/helpers.php';
require_once ROOT_PATH . '/function/users/events.php';
require_once ROOT_PATH . '/function/users/tickets.php';
require_once ROOT_PATH . '/function/users/contact.php';

function user_asset_url($path)
{
    return USER_ASSETS_URL . ltrim($path, '/');
}

function user_module_url($module)
{
    return BASE_URL . 'users/modules/' . trim($module, '/') . '/';
}
