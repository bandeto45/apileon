<?php

// Global helper functions

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        return \Apileon\Support\Helpers::env($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null) {
        return \Apileon\Support\Helpers::config($key, $default);
    }
}

if (!function_exists('response')) {
    function response(): \Apileon\Http\Response {
        return \Apileon\Support\Helpers::response();
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): \Apileon\Http\Response {
        return \Apileon\Support\Helpers::abort($code, $message);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void {
        \Apileon\Support\Helpers::dd(...$vars);
    }
}

if (!function_exists('now')) {
    function now(): string {
        return \Apileon\Support\Helpers::now();
    }
}
