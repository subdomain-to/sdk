<?php

namespace SubdomainTo\Exception;

class ApiException extends \RuntimeException
{
    private $statusCode;
    private $apiCode;
    private $requestId;
    private $responseBody;

    public function __construct(int $statusCode, string $apiCode, string $message, ?string $requestId = null, ?string $responseBody = null)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->apiCode = $apiCode;
        $this->requestId = $requestId;
        $this->responseBody = $responseBody;
    }

    public function statusCode(): int { return $this->statusCode; }
    public function apiCode(): string { return $this->apiCode; }
    public function requestId(): ?string { return $this->requestId; }
    public function responseBody(): ?string { return $this->responseBody; }
}
