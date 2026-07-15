<?php
namespace SubdomainTo\Model;
final class Health extends Model
{
    public function status(): string { return $this->string('status'); }
    public function service(): string { return $this->string('service'); }
    public function version(): string { return $this->string('version'); }
}
