<?php
namespace SubdomainTo\Model;
final class WidgetSession extends Model
{
    public function token(): string { return $this->string('token'); }
    public function expiresAt(): \DateTimeImmutable { return new \DateTimeImmutable($this->string('expires_at')); }
    public function expiresIn(): int { return $this->integer('expires_in'); }
}
