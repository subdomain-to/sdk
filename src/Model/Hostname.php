<?php
namespace SubdomainTo\Model;
final class Hostname extends Model
{
    public function id(): string { return $this->string('id'); }
    public function hostname(): string { return $this->string('hostname'); }
    public function type(): string { return $this->string('type'); }
    public function status(): string { return $this->string('status'); }
    public function dnsStatus(): string { return $this->string('dns_status'); }
    public function certificateStatus(): string { return $this->string('certificate_status'); }
    public function routingStatus(): string { return $this->string('routing_status'); }
    public function error(): ?string { return $this->nullableString('error'); }
    public function createdAt(): ?\DateTimeImmutable { $value = $this->nullableString('created_at'); return $value === null ? null : new \DateTimeImmutable($value); }
    public function origin(): ?Origin { $value = $this->nullableArray('origin'); return $value === null ? null : new Origin($value); }
    /** @return DnsRecord[] */
    public function dnsRecords(): array { return array_map(function (array $item): DnsRecord { return new DnsRecord($item); }, $this->arrayValue('dns_records')); }
}
