<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Prompt;

final readonly class PromptResult
{
    /**
     * @param list<array<string, mixed>> $messages
     */
    public function __construct(private string $description, private array $messages)
    {
    }

    public static function userText(string $description, string $text): self
    {
        return new self($description, [['role' => 'user', 'content' => ['type' => 'text', 'text' => $text]]]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['description' => $this->description, 'messages' => $this->messages];
    }
}
