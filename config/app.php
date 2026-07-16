<?php

declare(strict_types=1);

return [
    'is_dev' => (($_ENV['APP_ENV'] ?? 'dev') !== 'prod'),
    'https' => (($_ENV['APP_HTTPS'] ?? '0') === '1'),
    'app_name' => $_ENV['APP_NAME'] ?? 'CapsulePHP',
    'base_url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
    'base_path' => (static function (): string {
        $fromEnv = trim((string) (
            $_ENV['APP_BASE_PATH']
            ?? $_SERVER['APP_BASE_PATH']
            ?? getenv('APP_BASE_PATH')
            ?: ''
        ));
        if ($fromEnv !== '') {
            return $fromEnv;
        }

        require_once dirname(__DIR__) . '/bootstrap/base-path.php';

        return capsule_base_path_detect();
    })(),
    'password_min_length' => (int) ($_ENV['PASSWORD_MIN_LENGTH'] ?? 8),
    'dev_password' => $_ENV['DEV_PASSWORD'] ?? '',
    'client_password' => $_ENV['CLIENT_PASSWORD'] ?? '',
];
