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
        Api\ApiService\Middleware\CorsMiddleware::class,//跨域
        Api\ApiService\Middleware\RequestMethodAllowedMiddleware::class,//请求方式只能是post
        Api\ApiService\Middleware\FormatResponseMiddleware::class,//校验以及格式化返回数据
    ],
];
