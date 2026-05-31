<?php
namespace App\Middleware;

use App\Helpers\CSRF;
use App\Helpers\Response;

class CsrfMiddleware {

    public function handle($requestData) {
        CSRF::startSession();

        // Read X-CSRF-Token from request header
        $headers   = getallheaders();
        $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;

        if (!$csrfToken) {
            Response::json(403, false, "CSRF token missing.");
        }

        if (!CSRF::validate($csrfToken)) {
            Response::json(403, false, "CSRF token invalid or expired.");
        }

        return $requestData;
    }
}