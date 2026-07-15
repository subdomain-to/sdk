<?php
namespace SubdomainTo\Model;
final class Usage extends Model
{
    public function activeHostnames(): int { return $this->integer('active_hostnames'); }
    public function includedHostnames(): int { return $this->integer('included_hostnames'); }
    public function bandwidthGb(): float { return $this->number('bandwidth_gb'); }
    public function includedBandwidthGb(): int { return $this->integer('included_bandwidth_gb'); }
}
