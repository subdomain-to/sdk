<?php
namespace SubdomainTo\Model;
final class CheckoutSession extends Model
{
    public function id(): string { return $this->string('id'); }
    public function url(): string { return $this->string('url'); }
}
