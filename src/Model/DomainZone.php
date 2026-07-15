<?php
namespace SubdomainTo\Model;
final class DomainZone extends Model
{
    public function id(): string { return $this->string('id'); }
    public function baseDomain(): string { return $this->string('base_domain'); }
    public function status(): string { return $this->string('status'); }
    public function dnsStatus(): string { return $this->string('dns_status'); }
    public function certificateStatus(): string { return $this->string('certificate_status'); }
    public function error(): ?string { return $this->nullableString('error'); }
    /** @return DnsRecord[] */
    public function dnsRecords(): array { return array_map(function (array $item): DnsRecord { return new DnsRecord($item); }, $this->arrayValue('dns_records')); }
}
