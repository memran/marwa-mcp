<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Server;

final readonly class JsonRpcResponse
{
    /**
     * @param array<string, mixed>|null $error
     */
    private function __construct(
        public string|int|null $id,
        public mixed $result = null,
        public ?array $error = null
    ) {
    }

    public static function result(mixed $result, string|int|null $id): self
    {
        return new self($id, $result);
    }

    public static function error(int $code, string $message, string|int|null $id = null): self
    {
        return new self($id, null, ['code' => $code, 'message' => $message]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = ['jsonrpc' => '2.0', 'id' => $this->id];

        if ($this->error !== null) {
            $payload['error'] = $this->error;
            return $payload;
        }

        $payload['result'] = $this->result;
        return $payload;
    }
}
