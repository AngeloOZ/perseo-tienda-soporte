<?php

use Illuminate\Support\Facades\Route;
if (!function_exists('encrypt_openssl')) {
    function encrypt_openssl($msg, $key)
    {
        $key = hash('MD5', $key, TRUE);
        $encryptedMessage = openssl_encrypt($msg, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        return base64_encode($encryptedMessage);
    }
}

if (!function_exists('decrypt_openssl')) {
    function decrypt_openssl($msg, $key)
    {
        $data = base64_decode($msg);
        $key = hash('MD5', $key, TRUE);
        return openssl_decrypt($data, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
    }
}

if (!function_exists('areActiveRoutes')) {
    function areActiveRoutes(array $routes, $output = "menu-item-active")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }
    }
}

if (!function_exists('areActiveRoutesMenu')) {
    function areActiveRoutesMenu(array $routes, $output = "menu-item-open menu-item-here")
    {
        foreach ($routes as $route) {
            if (Route::currentRouteName() == $route) return $output;
        }
    }
}
