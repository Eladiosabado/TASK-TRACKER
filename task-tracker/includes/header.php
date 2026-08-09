<?php
/**
 * includes/header.php
 *
 * Shared <head> + opening <body> markup for frontend pages.
 * Expects an optional $pageTitle variable to be set before include.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Task Tracker';
$bodyDataPage = $bodyDataPage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo h(FRONTEND_URL); ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo h(FRONTEND_URL); ?>/assets/css/responsive.css">
</head>
<body<?php echo $bodyDataPage ? ' data-page="' . h($bodyDataPage) . '"' : ''; ?>>
