<?php
namespace SubdomainTo\Model;
final class WebhookEndpoint extends Model
{
    public function id(): string { return $this->string('id'); }
    public function url(): string { return $this->string('url'); }
    public function events(): array { return $this->arrayValue('events'); }
    public function active(): bool { return $this->boolean('active'); }
    public function secret(): string { return $this->string('secret'); }
}
