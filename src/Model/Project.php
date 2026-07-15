<?php
namespace SubdomainTo\Model;
final class Project extends Model
{
    public function id(): string { return $this->string('id'); }
    public function name(): string { return $this->string('name'); }
    public function slug(): string { return $this->string('slug'); }
    public function defaultOrigin(): ?Origin
    {
        $origin = $this->nullableArray('default_origin');
        return $origin === null ? null : new Origin($origin);
    }
}
