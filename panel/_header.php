<?php

defined('ACCESS') or exit('Not access');

$site_title = isset($site_title) ? $site_title : 'panel';
$site_head = isset($site_head) ? $site_head : '';

?><!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="none">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" defer></script>

  <link rel="stylesheet" href="/style.css">

  <?= $site_head ?>

  <title><?= e($site_title) ?></title>
</head>

<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container panel-shell">
    <a class="navbar-brand fw-semibold" href="/">miz</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-label="Menu">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mainNavbar" aria-labelledby="mainNavbarLabel">
  <div class="offcanvas-header">
    <span class="offcanvas-title fw-semibold" id="mainNavbarLabel">miz</span>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="list-group">
      <li class="list-group-item"><a href="/domain-list.php">domains</a></li>

      <li class="list-group-item"><a href="/file-manager/">file (www)</a></li>
      <li class="list-group-item"><a href="/file-manager-root/">file (root)</a></li>
      
      <li class="list-group-item"><a href="/phpmyadmin/">phpmyadmin</a></li>
      
      <li class="list-group-item"><a href="/log-html/">http logs</a></li>
      <li class="list-group-item"><a href="/server-stats">http stats</a></li>

      <li class="list-group-item"><a href="/crontab.php">crontab</a></li>
      <li class="list-group-item"><a href="/ftptab.php">ftp</a></li>
      <li class="list-group-item"><a href="/cmd.php">run cmd</a></li>

      <li class="list-group-item"><a href="/settings.php">settings</a></li>
    </ul>
  </div>
</div>

<div class="container panel-shell py-3">
