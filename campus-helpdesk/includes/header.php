<?php
// header.php
// Top part of every page (head, site title and navigation).
// Set $pageTitle and $currentPage before including this file.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Campus IT Help Desk</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container">
        <a href="index.php" class="logo">Campus IT Help Desk</a>
        <nav>
            <a href="index.php" <?php if ($currentPage === 'index') echo 'class="active"'; ?>>Submit a Request</a>
            <a href="requests.php" <?php if ($currentPage === 'requests') echo 'class="active"'; ?>>View Requests</a>
        </nav>
    </div>
</header>

<main class="container">
