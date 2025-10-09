<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="TERDEPAN API Documentation",
 *     description="Dokumentasi API Sistem TERDEPAN (e-Kinerja BAPPEDA)",
 *     @OA\Contact(
 *         email="dev@terdepan.local"
 *     )
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class SwaggerController
{
    // Hanya untuk anotasi global Swagger
}
