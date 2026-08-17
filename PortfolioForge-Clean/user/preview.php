<?php
// user/preview.php

require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$pdo = getDBConnection();

$portfolio = getOrCreateUserPortfolio($user['user_id'], $pdo);

$_GET['slug'] = $portfolio['portfolio_slug'];
$_GET['is_preview'] = 'true';

require_once __DIR__ . '/../portfolio/view.php';

