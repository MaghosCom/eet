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

use Maghos\Eet\Model\Api\Exception\CertificateException;

/**
 * Cash register certificate loaded from PKCS#12 data, or from PEM data with the certificate and private key.
 */
class Certificate
{
    private const PEM_CERTIFICATE = '-----BEGIN CERTIFICATE-----';

    /** @var string */
    private string $certificate;

    /** @var string */
    private string $privateKey;

    /**
     * Parse the certificate data.
     *
     * @param string $data
     * @param string $password
     * @throws CertificateException
     */
    public function __construct(string $data, string $password)
    {
        $certs = [];
        if ($data !== '' && openssl_pkcs12_read($data, $certs, $password)) {
            $this->certificate = $certs['cert'];
            $this->privateKey = $certs['pkey'];
            return;
        }

        // PKCS#12 files issued by EET CA use RC2-40, which OpenSSL 3 reads only with the legacy provider
        $key = strpos($data, self::PEM_CERTIFICATE) !== false ? openssl_pkey_get_private($data, $password) : false;
        if ($key === false || !openssl_x509_export($data, $certificate) || !openssl_pkey_export($key, $privateKey)) {
            $this->clearErrors();
            throw new CertificateException(
                __('The EET certificate cannot be read. Check the file and password, or convert it to PEM.')
            );
        }

        $this->certificate = $certificate;
        $this->privateKey = $privateKey;
    }

    /**
     * Get the certificate as base64 encoded DER.
     *
     * @return string
     */
    public function getCertificateBase64(): string
    {
        return (string)preg_replace('/-----[A-Z ]+-----|\s+/', '', $this->certificate);
    }

    /**
     * Get the EIC from the certificate subject common name.
     *
     * @return string
     */
    public function getEic(): string
    {
        $info = openssl_x509_parse($this->certificate);
        return (string)($info['subject']['CN'] ?? '');
    }

    /**
     * Sign data with the private key using RSA-SHA256 and return the raw signature.
     *
     * @param string $data
     * @return string
     * @throws CertificateException
     */
    public function sign(string $data): string
    {
        $signature = '';
        if (!openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            $this->clearErrors();
            throw new CertificateException(__('The EET message cannot be signed with the certificate.'));
        }

        return $signature;
    }

    /**
     * Empty the OpenSSL error queue so the errors do not leak into later OpenSSL calls.
     *
     * @return void
     */
    private function clearErrors(): void
    {
        while (openssl_error_string() !== false) {
            continue;
        }
    }
}
