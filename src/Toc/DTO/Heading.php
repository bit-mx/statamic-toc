<?php

declare(strict_types=1);

namespace BitMx\StatamicToc\Toc\DTO;

final class Heading
{
    /**
     * @param  array<int, Heading>  $children
     */
    public function __construct(
        public readonly string $text,
        public readonly int $level,
        public readonly string $id,
        public readonly array $children = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $withChildren = true): array
    {
        $data = [
            'text' => $this->text,
            'level' => $this->level,
            'id' => $this->id,
            'url' => '#'.$this->id,
        ];

        if ($withChildren) {
            $data['children'] = array_map(
                static fn (Heading $heading): array => $heading->toArray(true),
                $this->children,
            );
        }

        return $data;
    }
}
