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

if (!function_exists('app_debug')) {
    function app_debug(): bool {
        return filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);
    }
}

if (!function_exists('validate')) {
    function validate(array $data, array $rules, array $messages = []): array {
        return \Apileon\Validation\Validator::make($data, $rules, $messages)->validate();
    }
}

if (!function_exists('hash_password')) {
    function hash_password(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ]);
    }
}

if (!function_exists('verify_password')) {
    function verify_password(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}

if (!function_exists('generate_token')) {
    function generate_token(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('sanitize_input')) {
    function sanitize_input($input): string {
        if (is_array($input)) {
            return implode(',', array_map('sanitize_input', $input));
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('str_limit')) {
    function str_limit(string $value, int $limit = 100, string $end = '...'): string {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}

if (!function_exists('str_slug')) {
    function str_slug(string $title, string $separator = '-'): string {
        $title = preg_replace('![^\\pL\\pN\\s]+!u', '', mb_strtolower($title));
        $title = preg_replace('!\\s+!u', $separator, $title);
        return trim($title, $separator);
    }
}

if (!function_exists('array_get')) {
    function array_get(array $array, string $key, $default = null) {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}

if (!function_exists('array_set')) {
    function array_set(array &$array, string $key, $value): array {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if (!function_exists('json_response')) {
    function json_response(array $data, int $status = 200): \Apileon\Http\Response {
        return \Apileon\Http\Response::json($data, $status);
    }
}

if (!function_exists('success_response')) {
    function success_response($data = null, string $message = 'Success', int $status = 200): \Apileon\Http\Response {
        return json_response([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }
}

if (!function_exists('error_response')) {
    function error_response(string $error, string $message = '', int $status = 400, array $errors = []): \Apileon\Http\Response {
        $response = [
            'success' => false,
            'error' => $error,
            'message' => $message ?: $error
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return json_response($response, $status);
    }
}

if (!function_exists('db')) {
    function db(): \Apileon\Database\QueryBuilder {
        return new \Apileon\Database\QueryBuilder();
    }
}

if (!function_exists('migrate')) {
    function migrate(): void {
        $runner = new \Apileon\Database\MigrationRunner();
        $runner->migrate();
    }
}

if (!function_exists('seed')) {
    function seed(): void {
        $runner = new \Apileon\Database\MigrationRunner();
        $runner->seed();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = generate_token();
        }
        return $_SESSION['_token'];
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token(string $token): bool {
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }
}
