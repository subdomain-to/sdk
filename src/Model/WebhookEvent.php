<?php
namespace SubdomainTo\Model;
final class WebhookEvent extends Model
{
    public function id(): string { return $this->string('id'); }
    public function event(): string { return $this->string('event'); }
    public function createdAt(): \DateTimeImmutable { return new \DateTimeImmutable($this->string('created_at')); }
    public function data(): array { return $this->arrayValue('data'); }
}
