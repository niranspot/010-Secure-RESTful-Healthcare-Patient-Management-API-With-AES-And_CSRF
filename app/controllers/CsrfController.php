<?php
namespace App\Controllers;

use App\Helpers\CSRF;
use App\Helpers\Response;

class CsrfController {

    public function token($request) {
        $token = CSRF::generate();

        Response::json(200, true, "CSRF token generated.", [
            "csrf_token" => $token,
            "expires_in" => CSRF_EXPIRY
        ]);
    }
}