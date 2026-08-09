<?php
/**
 * includes/footer.php
 *
 * Shared closing markup for frontend pages, including the toast
 * notification container and the base JS include.
 * Expects an optional $pageScripts array of extra script paths.
 */
$pageScripts = $pageScripts ?? [];
?>
    <div id="toast-container" class="toast-container" aria-live="polite"></div>

    <script src="<?php echo h(FRONTEND_URL); ?>/assets/js/utils.js"></script>
    <script src="<?php echo h(FRONTEND_URL); ?>/assets/js/api.js"></script>
    <script src="<?php echo h(FRONTEND_URL); ?>/assets/js/auth.js"></script>
    <?php foreach ($pageScripts as $script): ?>
    <script src="<?php echo h(FRONTEND_URL . $script); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
