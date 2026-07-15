<?php
namespace SubdomainTo\Model;
final class CollectionResult
{
    private $items;
    private $hasMore;
    private $nextCursor;
    public function __construct(array $items, bool $hasMore, ?string $nextCursor) { $this->items = $items; $this->hasMore = $hasMore; $this->nextCursor = $nextCursor; }
    public function items(): array { return $this->items; }
    public function hasMore(): bool { return $this->hasMore; }
    public function nextCursor(): ?string { return $this->nextCursor; }
}
