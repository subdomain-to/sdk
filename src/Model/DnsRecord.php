<?php
namespace SubdomainTo\Model;
final class DnsRecord extends Model
{
    public function type(): string { return $this->string('type'); }
    public function name(): string { return $this->string('name'); }
    public function value(): string { return $this->string('value'); }
}
