<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function accountLogin($email, $password){
    
}

function randomGen($characters, $length) {
    $charactersLength = strlen($characters);
    $random = '';
    for ($i = 0; $i < $length; $i++) {
        $random .= $characters[rand(0, $charactersLength - 1)];
    }
    return $random;
}

function generatePassword() {
    return randomGen('123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz', 8);
}

function generateVerifyCode() {
    return randomGen('123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz', 4);
}

function sendVerificationEmail($CI, $email, $user) {
    $CI->email->from('dappleros-ico@valigo.net', 'Valigo');
    $CI->email->to($email);
    $CI->email->subject('Valigo - Please Verify Your Account');
    $message = '';
    $message .= 'Please login to your account at <a href='.VALIGO_WEBSITE_LINK.'>'.VALIGO_WEBSITE_LINK.'</a>.<br/>';
    $message .= 'Then enter your verification code as <strong>' . $user->verifyCode . '</strong>.';
    $CI->email->message($message);

    return $CI->email->send();
}

function sendForgetPasswordEmail($CI, $email, $newPassword) {
    $CI->email->from('dappleros-ico@valigo.net', 'Valigo');
    $CI->email->to($email);
    $CI->email->subject('Valigo - Reset Password');
    $CI->email->message('Your new password is <strong>' . $newPassword . '</strong>.');

    return $CI->email->send();
}

function sendAdminVerificationEmail($CI, $user) {
    $CI->email->from('dappleros-ico@valigo.net', 'Valigo');
    $CI->email->to($user->email);
    $CI->email->subject('[Dappler OS ICO] You have passed the KYC verification');

    $message = "";
    $message .= "Dear ".$user->fullname.",<br/>";
    $message .= "<br/>";
    $message .= "We are happy to inform you that you have passed the KYC verification.<br/>";
    $message .= "Please follow the guidelines set out in the <a href='https://valigo.net/dappleros/dappler_os_ico_contribution_information_package.pdf'>Dappler OS ICO Contribution Information Package</a> for contribution instructions and other relevant information.<br/>";
    $message .= "<br/>";
    $message .= "Best regards,<br/>";
    $message .= "Valigo Team<br/>";

    $CI->email->message($message);
    return $CI->email->send();
}
?>