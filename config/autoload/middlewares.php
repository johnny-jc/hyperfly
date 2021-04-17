<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    'http' => [
        Api\ApiTool\Middleware\CorsMiddleware::class,
        Api\ApiTool\Middleware\RequestMethodAllowedMiddleware::class,
        Api\ApiTool\Middleware\AuthMiddleware::class,
        Api\ApiTool\Middleware\FormatResponseMiddleware::class,
    ],
];
