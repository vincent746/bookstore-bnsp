<?php

if (defined('ADMIN_INIT')) {
    return;
}

define('ADMIN_INIT', true);

$root_path = dirname(__DIR__, 2);
$doc_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$root_normalized = str_replace('\\', '/', $root_path);
$base_url = str_replace($doc_root, '', $root_normalized);
$base_url = '/' . trim($base_url, '/') . '/';

define('ROOT_PATH', $root_path);
define('BASE_URL', $base_url);
define('ADMIN_URL', BASE_URL . 'admin/');
define('LOGIN_URL', BASE_URL . 'login');
define('ASSETS_URL', BASE_URL . 'assets/');
define('ADMIN_INCLUDES', __DIR__ . '/');

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/function/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function admin_module_url($module)
{
    return BASE_URL . 'admin/modules/' . trim($module, '/') . '/';
}

function asset_url($path)
{
    return ASSETS_URL . ltrim($path, '/');
}

function tanggal_id($timestamp = null)
{
    $timestamp = $timestamp ?? time();
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    return $hari[(int) date('w', $timestamp)] . ', '
        . date('j', $timestamp) . ' '
        . $bulan[(int) date('n', $timestamp)] . ' '
        . date('Y', $timestamp);
}
