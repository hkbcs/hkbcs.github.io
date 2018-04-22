<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once (APPPATH . 'core/encrypt.php');

function ensureAuthorized($CI, $expectedUserId)
{
    $headers = $CI->input->request_headers();
    $authorization = $headers['Authorization'];
    if (!$authorization) {
        throw new UnauthorizedException('Unauthorized access');
    }
    $authorizationObj = explode(':', base64_decode(substr($authorization, 6)));
    $email = $authorizationObj[0];
    $password = $authorizationObj[1];

    $CI->load->model('valigo_account');
    $user = $CI->valigo_account->selectAccountByEmailPassword($email, $password);
    $valid = $user->id == $expectedUserId;
    if (!$valid) {
        throw new UnauthorizedException('Unauthorized access');
    }
}

function ensureAuthorizedAdmin($CI) {
    $headers = $CI->input->request_headers();
    $authorization = $headers['Authorization'];
    if (!$authorization) {
        throw new UnauthorizedException('Unauthorized access, no header, headers: '.print_r($headers, true));
    }
    $authorizationObj = explode(':', base64_decode(substr($authorization, 6)));
    $username = $authorizationObj[0];
    $password = $authorizationObj[1];

    $valid = $username == ADMIN_USERNAME && verify(ADMIN_PASSWORD, $password);
    if (!$valid) {
        throw new UnauthorizedException('Unauthorized access, verify: '.verify(ADMIN_PASSWORD, $password));
    }
}
