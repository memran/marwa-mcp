<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Transport;

interface TransportInterface
{
    public function listen(): void;
}
