<?php
// app/Core/Response.php
declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(
        array $data,
        int $statusCode = 200,
        array $headers = []
    ): void {
        header('Content-Type: application/json', true, $statusCode);
        
        foreach ($headers as $header => $value) {
            header("$header: $value");
        }
        
        echo json_encode($data, JSON_THROW_ON_ERROR);
        exit;
    }
    
    public static function error(
        string $message,
        int $statusCode = 400,
        ?array $details = null
    ): void {
        self::json([
            'error' => $message,
            'details' => $details
        ], $statusCode);
    }
}