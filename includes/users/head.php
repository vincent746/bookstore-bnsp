<?php

if (!defined('USER_INIT')) {
    require_once __DIR__ . '/init.php';
}

$page_title = $page_title ?? 'BookStore';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="<?= user_asset_url('css/bootstrap.min.css') ?>" rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
        rel="stylesheet" />
    <link href="<?= user_asset_url('css/bootstrap-icons.min.css') ?>" rel="stylesheet" />
    <link rel="stylesheet" href="<?= user_asset_url('css/style.css') ?>">
</head>

<body>
