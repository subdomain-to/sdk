<?php
namespace SubdomainTo\Model;
final class Destination extends Model
{
    public function id(): string { return $this->string('id'); }
    public function url(): string { return $this->string('url'); }
    public function host(): string { return $this->string('host'); }
    public function port(): int { return $this->integer('port'); }
    public function isDefault(): bool { return $this->boolean('default'); }
}
