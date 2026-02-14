<?php
require_once __DIR__ . '/data.php';

if (!isset($pageTitle)) {
    $pageTitle = $siteTitle;
}

if (!isset($currentPage)) {
    $currentPage = 'home';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($siteDescription, ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="assets/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/global.css">
</head>
<body class="page-is-loading">
    <div class="background-decor" aria-hidden="true">
        <svg viewBox="0 0 1920 1080" preserveAspectRatio="xMidYMid slice">
            <circle class="draw-circle circle-a" cx="240" cy="180" r="110" pathLength="100"></circle>
            <circle class="draw-circle circle-b" cx="1540" cy="210" r="145" pathLength="100"></circle>
            <circle class="draw-circle circle-c" cx="420" cy="760" r="180" pathLength="100"></circle>
            <circle class="draw-circle circle-d" cx="1440" cy="720" r="125" pathLength="100"></circle>
            <circle class="draw-circle circle-e" cx="980" cy="500" r="210" pathLength="100"></circle>
            <circle class="draw-circle small circle-f" cx="680" cy="250" r="42" pathLength="100"></circle>
            <circle class="draw-circle small circle-g" cx="1180" cy="300" r="30" pathLength="100"></circle>
            <circle class="draw-circle small circle-i" cx="1610" cy="560" r="28" pathLength="100"></circle>
            <circle class="draw-circle small circle-j" cx="1080" cy="820" r="34" pathLength="100"></circle>
            <path class="draw-cross cross-a" d="M 260 360 L 330 430 M 330 360 L 260 430" pathLength="100"></path>
            <path class="draw-cross cross-b" d="M 1500 420 L 1580 500 M 1580 420 L 1500 500" pathLength="100"></path>
            <path class="draw-cross small cross-d" d="M 640 640 L 682 682 M 682 640 L 640 682" pathLength="100"></path>
            <path class="draw-cross small cross-e" d="M 1280 200 L 1320 240 M 1320 200 L 1280 240" pathLength="100"></path>
        </svg>
    </div>

    <div class="page-transition-overlay" aria-hidden="true"></div>

    <div class="site-content">
        <?php include __DIR__ . '/header.php'; ?>
