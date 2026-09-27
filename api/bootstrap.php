<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$config = require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'This endpoint accepts POST requests only.', 405);
}

function respond(bool $success, string $message, int $status = 200, array $extra = []): void
{
    http_response_code($status);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_SLASHES);
    exit;
}

function input_value(string $key, int $maxLength = 500): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    $value = preg_replace('/\s+/', ' ', $value) ?? '';

    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $maxLength);
    }

    return substr($value, 0, $maxLength);
}

function require_value(string $key, string $label, int $maxLength = 500): string
{
    $value = input_value($key, $maxLength);

    if ($value === '') {
        respond(false, $label . ' is required.', 422);
    }

    return $value;
}

function require_email(string $key = 'email'): string
{
    $email = require_value($key, 'Email', 255);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, 'Please enter a valid email address.', 422);
    }

    return strtolower($email);
}

function reject_spam(): void
{
    if (input_value('website', 255) !== '') {
        respond(true, 'Thanks. Your request has been received.');
    }
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function enforce_rate_limit(string $bucket): void
{
    global $config;

    $limit = $config['rate_limit'];
    $now = time();
    $window = (int) $limit['window_seconds'];
    $maxAttempts = (int) $limit['max_attempts'];
    $key = preg_replace('/[^a-z0-9_.-]/i', '-', $bucket . '-' . client_ip()) ?? 'default';
    $path = storage_path('rate-' . $key . '.json');
    $attempts = [];

    if (is_file($path)) {
        $attempts = json_decode((string) file_get_contents($path), true);
        $attempts = is_array($attempts) ? $attempts : [];
    }

    $attempts = array_values(array_filter($attempts, static function ($timestamp) use ($now, $window): bool {
        return is_int($timestamp) && $timestamp > ($now - $window);
    }));

    if (count($attempts) >= $maxAttempts) {
        respond(false, 'Too many attempts. Please wait a few minutes and try again.', 429);
    }

    $attempts[] = $now;
    file_put_contents($path, json_encode($attempts), LOCK_EX);
}

function storage_path(string $filename): string
{
    global $config;

    $directory = $config['storage_dir'];

    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        respond(false, 'Storage is not writable yet. Please try again later.', 500);
    }

    return $directory . DIRECTORY_SEPARATOR . $filename;
}

function append_json_record(string $filename, array $record): void
{
    $record['created_at'] = gmdate('c');
    $record['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $record['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $encoded = json_encode($record, JSON_UNESCAPED_SLASHES);

    if ($encoded === false || file_put_contents(storage_path($filename), $encoded . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        respond(false, 'We could not save your request. Please try again.', 500);
    }
}
