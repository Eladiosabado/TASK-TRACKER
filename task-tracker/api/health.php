<?php
/**
 * GET /api/health.php
 *
 * Public endpoint. No authentication required.
 * Used to verify the API and PHP environment are running.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

success_response(null, 'API is running');
