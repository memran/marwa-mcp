<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Transport;

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class StdioTransport implements TransportInterface
{
    private mixed $input;

    private mixed $output;

    private mixed $errorOutput;

    public function __construct(
        private readonly JsonRpcHandler $handler,
        mixed $input = null,
        mixed $output = null,
        mixed $errorOutput = null,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
        $this->input = $input ?? STDIN;
        $this->output = $output ?? STDOUT;
        $this->errorOutput = $errorOutput ?? STDERR;
    }

    public function listen(): void
    {
        while (($line = fgets($this->input)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            try {
                fwrite($this->output, $this->handler->handle($line) . PHP_EOL);
            } catch (Throwable $throwable) {
                $this->logger->error('Stdio transport failure.', ['exception' => $throwable]);
                fwrite($this->errorOutput, 'Stdio transport failure.' . PHP_EOL);
            }
        }
    }
}
