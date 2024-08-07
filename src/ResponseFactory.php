<?php

declare(strict_types=1);

namespace Bpartner\ApiResponse;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class ResponseFactory
{
    public function __construct(protected ResponseManager $builder) {}

    public function ok(mixed $meta = [], array $headers = []): Responsable
    {
        return $this->builder->response('OK', meta: $meta, headers: $headers);
    }

    public function response(mixed $payload, int $status, mixed $meta = [], array $headers = []): Responsable
    {
        return  $this->builder->response($payload, status: $status, meta: $meta, headers: $headers);
    }

    public function success(mixed $payload = [], mixed $meta = [], array $headers = [], int $status = 200): Responsable
    {
        return $this->builder->response($payload, status: $status, meta: $meta, headers: $headers);
    }

    public function notFound(mixed $meta = [], array $headers = []): Responsable
    {
        return $this->builder->response(
            null,
            status: Response::HTTP_NOT_FOUND,
            meta: array_merge(['message' => 'Not Found'], $meta),
            headers: $headers,
        );
    }

    public function error(
        mixed $payload = [],
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $meta = [],
        array $headers = [],
    ): Responsable {
        return $this->builder->error($payload, $status, $meta, $headers);
    }
}
