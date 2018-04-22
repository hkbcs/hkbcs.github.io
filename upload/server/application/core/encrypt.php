<?php

function encrypt($data) {
    return password_hash($data, PASSWORD_BCRYPT);
}

function verify($data, $hash) {
    return password_verify($data, $hash);
}