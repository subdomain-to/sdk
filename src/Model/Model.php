<?php

namespace SubdomainTo\Model;

abstract class Model
{
    private $data;

    final public function __construct(array $data) { $this->data = $data; }
    final public function toArray(): array { return $this->data; }

    final protected function string(string $key): string { return (string) ($this->data[$key] ?? ''); }
    final protected function nullableString(string $key): ?string { return isset($this->data[$key]) ? (string) $this->data[$key] : null; }
    final protected function integer(string $key): int { return (int) ($this->data[$key] ?? 0); }
    final protected function number(string $key): float { return (float) ($this->data[$key] ?? 0); }
    final protected function boolean(string $key): bool { return (bool) ($this->data[$key] ?? false); }
    final protected function arrayValue(string $key): array { return is_array($this->data[$key] ?? null) ? $this->data[$key] : []; }
    final protected function nullableArray(string $key): ?array { return is_array($this->data[$key] ?? null) ? $this->data[$key] : null; }
}
