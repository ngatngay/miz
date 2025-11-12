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

    <link href="https://static.ngatngay.net/js/layui/css/layui.css" rel="stylesheet" />
    <script src="https://static.ngatngay.net/js/layui/layui.js"></script>
    <script src="https://static.ngatngay.net/js/layui/nightmare.layui.table.js"></script>
   
    <script src="https://static.ngatngay.net/js/nightmare/nightmare.js"></script>
    <link rel="stylesheet" href="style.css">

    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="/asset/favicon/ms-icon-144x144.png" />

<script>
var $ = layui.$;
</script>

  <?= $site_head ?>

  <title><?= htmlspecialchars($site_title) ?></title>
</head>

<body>

<div class="container-fluid p-0">

<div class="title"><a href="/">Home</a></div>

<div class="content">
    &bull; <a href="/file-manager/">file manager</a>
    <hr>
    &bull; <a href="/phpmyadmin/">phpmyadmin</a>
    <hr>
    &bull; <a href="/log-html/">logs</a>
    <hr>
    &bull; <a href="/server-status">status</a>
</div>
