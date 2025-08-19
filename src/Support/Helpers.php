<?php

namespace Apileon\Support;

class Helpers
{
    public static function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }

    public static function config(string $key, $default = null)
    {
        global $app;
        return $app ? $app->config($key, $default) : $default;
    }

    public static function response(): \Apileon\Http\Response
    {
        return new \Apileon\Http\Response();
    }

    public static function abort(int $code, string $message = ''): \Apileon\Http\Response
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
        ];

        $message = $message ?: ($messages[$code] ?? 'Error');

        return \Apileon\Http\Response::json([
            'error' => $message,
            'code' => $code
        ], $code);
    }

    public static function dd(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }

    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}
