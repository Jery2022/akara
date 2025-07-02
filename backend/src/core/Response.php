<?php
namespace Core;

class Response
{
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $statusCode < 400 ? 'success' : 'error',
            'data'   => $data,
        ]);
    }

    public static function error(string $message, int $statusCode = 400): void
    {
        self::json(['message' => $message], $statusCode);
    }
}
