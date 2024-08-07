<?php

declare(strict_types=1);

namespace Bpartner\ApiResponse;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse implements Responsable
{
    private mixed $data = null;
    private array $headers = [];
    private int $status = Response::HTTP_OK;


    public function toResponse($request): JsonResponse
    {
        return new JsonResponse(
            data: $this->data,
            status: $this->status,
            headers: $this->headers,
        );
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    public function setData(mixed $data): static
    {
        $this->data = $data;

        return $this;
    }
}
