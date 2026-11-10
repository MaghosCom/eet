<?php
/**
 *
 * Maghos_Eet Magento 2 extension
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @category Maghos
 * @package Maghos_Eet
 * @copyright Copyright (c) 2026 Maghos
 * @license http://www.maghos.com/business-license
 * @author Magento dev team <support@maghos.com>
 */
declare(strict_types=1);

namespace Maghos\Eet\Model\Api;

/**
 * Answer of the EET server: acknowledgement (POK) or error, with optional warnings.
 */
class Response
{
    public const ERROR_TEMPORARY = -1;
    public const ERROR_VERIFICATION_OK = 0;

    /** @var string|null */
    private ?string $pok;

    /** @var int|null */
    private ?int $errorCode;

    /** @var string */
    private string $message;

    /** @var string[] */
    private array $warnings;

    /** @var bool */
    private bool $test;

    /** @var string */
    private string $transactionId;

    /**
     * @param string|null $pok
     * @param int|null $errorCode
     * @param string $message
     * @param string[] $warnings
     * @param bool $test
     * @param string $transactionId
     */
    public function __construct(
        ?string $pok = null,
        ?int $errorCode = null,
        string $message = '',
        array $warnings = [],
        bool $test = false,
        string $transactionId = ''
    ) {
        $this->pok = $pok;
        $this->errorCode = $errorCode;
        $this->message = $message;
        $this->warnings = $warnings;
        $this->test = $test;
        $this->transactionId = $transactionId;
    }

    /**
     * Get the acknowledgement code, null for error responses.
     *
     * @return string|null
     */
    public function getPok(): ?string
    {
        return $this->pok;
    }

    /**
     * Get the error code, null for acknowledgements.
     *
     * @return int|null
     */
    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    /**
     * Get the error text.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get warnings as "code: text" strings.
     *
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Check whether the response came from the playground.
     *
     * @return bool
     */
    public function isTest(): bool
    {
        return $this->test;
    }

    /**
     * Get the X-Global-Transaction-Id header used by EET support for tracing.
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * Check whether the sale was accepted, or the verification mode message processed without errors.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->pok !== null || $this->errorCode === self::ERROR_VERIFICATION_OK;
    }
}
