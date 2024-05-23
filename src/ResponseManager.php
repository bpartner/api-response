<?php

declare(strict_types=1);

namespace Bpartner\ApiResponse;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

final class ResponseManager
{
    private static ?string $wrapper = 'data';
    private static string $paginate = 'paginate';
    private static bool $withMeta = true;
    private static bool $withStatus = true;
    private static bool $explainException = true;

    /**
     * @param  ApiResponse  $response
     */
    public function __construct(protected ApiResponse $response) {}

    /**
     * @param  string|null  $wrapper
     *
     * @return void
     */
    public static function wrapper(?string $wrapper = null): void
    {
        self::$wrapper = $wrapper;
    }

    /**
     * @param  string  $paginate
     *
     * @return void
     */
    public static function paginate(string $paginate): void
    {
        self::$paginate = $paginate;
    }

    /**
     * @return void
     */
    public static function withoutMeta(): void
    {
        self::$withMeta = false;
    }

    /**
     * @return void
     */
    public static function withoutStatus(): void
    {
        self::$withStatus = false;
    }

    /**
     * @return void
     */
    public static function disableException(): void
    {
        self::$explainException = false;
    }

    /**
     * @param  mixed  $payload
     * @param  int    $status
     * @param  mixed  $meta
     * @param  array  $headers
     *
     * @return Responsable
     */
    public function response(
        mixed $payload,
        int $status = Response::HTTP_OK,
        mixed $meta = [],
        array $headers = [],
    ): Responsable {
        return $this->response
            ->setHeaders($headers)
            ->setStatus($status)
            ->setData($this->makeData($payload, $this->getStatus($status), $meta));
    }

    /**
     * @param  mixed  $payload
     * @param  int    $status
     * @param  mixed  $meta
     * @param  array  $headers
     *
     * @return Responsable
     */
    public function error(
        mixed $payload,
        int $status = Response::HTTP_BAD_REQUEST,
        mixed $meta = [],
        array $headers = [],
    ): Responsable {
        if ($payload instanceof Exception) {
            $meta['exception'] = class_basename($payload);
            $meta['message'] = $payload->getMessage();

            if ($payload instanceof ValidationException) {
                $meta['errors'] = $payload->errors();
            }

            if (self::$explainException) {
                $meta = array_merge_recursive($meta, [
                    'trace' => $payload->getTrace(),
                ]);
            }
        }

        return $this->response(
            [],
            $status,
            $meta,
            $headers,
        );
    }

    /**
     * @param  mixed   $payload
     * @param  string  $status
     * @param  mixed   $meta
     *
     * @return array
     */
    private function makeData(mixed $payload, string $status, mixed $meta): array
    {
        $formatted = $this->formatData($payload);

        if (self::$withMeta) {
            $metaData = $this->parseMeta($meta);
            $formatted['meta'] = array_merge_recursive($formatted['meta'] ?? [], $metaData);
        }

        if (self::$withStatus) {
            $formatted['status'] = $status;
        }

        return $this->filter($formatted);
    }

    /**
     * @param  mixed  $payload
     *
     * @return array
     */
    private function formatData(mixed $payload): array
    {
        $response = [];
        $content = $this->parsePayload($payload);

        if ( ! self::$wrapper) {
            return Arr::wrap($content);
        }

        if (self::$wrapper) {
            $response[self::$wrapper] = [];
        }

        return array_merge_recursive($response, $content ?? []);
    }

    /**
     * @param  int  $status
     *
     * @return string
     */
    private function getStatus(int $status): string
    {
        if ($status >= 200 && $status <= 299) {
            return 'success';
        }

        return 'error';
    }

    /**
     * @param  mixed  $payload
     *
     * @return array|JsonSerializable|Arrayable|null
     */
    private function parsePayload(mixed $payload): array|JsonSerializable|Arrayable|null
    {
        if ($this->isPageableResource($payload)) {
            $paginated = $payload->resource->toArray();

            return $this->formatPagination($paginated);
        }

        if ($payload instanceof LengthAwarePaginator) {
            return $this->formatPagination($payload->toArray());
        }

        return $this->toArray($payload);
    }

    /**
     * @param $paginated
     *
     * @return array
     */
    private function formatPagination($paginated): array
    {
        return [
            self::$wrapper ?? 'data' => $paginated['data'],
            'paginate'               => Arr::except(
                $paginated,
                array_merge(['data'], config('api-response.pagination.exclude_fields')),
            ),
        ];
    }

    /**
     * @param  string|array|JsonSerializable|Arrayable|null  $payload
     *
     * @return array|JsonSerializable|Arrayable|null
     */
    private function toArray(
        string|array|JsonSerializable|Arrayable|null $payload,
    ): array|JsonSerializable|Arrayable|null {
        if (is_string($payload)) {
            $payload = [self::$wrapper ?? config('api-response.string_field_wrapper') => $payload];
        }

        if ($payload instanceof Arrayable) {
            return $payload->toArray();
        }

        if ($payload instanceof JsonSerializable) {
            return $payload->jsonSerialize();
        }

        return $payload;
    }

    /**
     * @param  mixed  $payload
     *
     * @return bool
     */
    private function isPageableResource(mixed $payload): bool
    {
        return ($payload instanceof AnonymousResourceCollection && $payload->resource instanceof LengthAwarePaginator);
    }

    /**
     * @param  mixed  $meta
     *
     * @return array
     */
    private function parseMeta(mixed $meta): array
    {
        if (is_array($meta)) {
            return $meta;
        }

        if (is_string($meta)) {
            if (str($meta)->isJson()) {
                return json_decode($meta, true);
            }

            return Arr::wrap($meta);
        }

        if ($meta instanceof Arrayable) {
            return $meta->toArray();
        }

        return (array) $meta;
    }

    private function filter(mixed &$payload): array
    {
        foreach ($payload as $key => &$value) {
            if (is_array($value)) {
                $value = $this->filter($value);
            }
            if (empty($value) && $key !== self::$wrapper) {
                unset($payload[$key]);
            }
        }

        return $payload;
    }
}
