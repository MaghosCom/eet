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

namespace Maghos\Eet\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public const XML_PATH_ENABLED = 'eet/eet/enabled';
    public const XML_PATH_TEST_ENV = 'eet/eet/testEnv';
    public const XML_PATH_EIC = 'eet/eet/eic';
    public const XML_PATH_UNIT_ID = 'eet/eet/unitId';
    public const XML_PATH_CHECKOUT_ID = 'eet/eet/checkoutId';
    public const XML_PATH_CERT_FILE = 'eet/eet/certFile';
    public const XML_PATH_CERT_PASSWORD = 'eet/eet/certPassword';
    public const XML_PATH_PAYMENT_METHODS = 'eet/eet/paymentMethods';

    /**
     * Get module enabled state.
     *
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return bool
     */
    public function isEnabled($scopeCode = null, bool $websiteScope = false): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_ENABLED, $scopeCode, $websiteScope);
    }

    /**
     * Check whether the EET playground environment is used.
     *
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return bool
     */
    public function isTestEnv($scopeCode = null, bool $websiteScope = false): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_TEST_ENV, $scopeCode, $websiteScope);
    }

    /**
     * Get taxpayer EIC.
     *
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return string
     */
    public function getEic($scopeCode = null, bool $websiteScope = false): string
    {
        return trim((string)$this->getConfig(self::XML_PATH_EIC, $scopeCode, $websiteScope));
    }

    /**
     * Get registrating unit id assigned in DIS+.
     *
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return string
     */
    public function getUnitId($scopeCode = null, bool $websiteScope = false): string
    {
        return trim((string)$this->getConfig(self::XML_PATH_UNIT_ID, $scopeCode, $websiteScope));
    }

    /**
     * Get checkout id.
     *
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return string
     */
    public function getCheckoutId($scopeCode = null, bool $websiteScope = false): string
    {
        return trim((string)$this->getConfig(self::XML_PATH_CHECKOUT_ID, $scopeCode, $websiteScope));
    }

    /**
     * Get the content of the uploaded certificate (PKCS#12 or PEM), or empty string when missing.
     *
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return string
     */
    public function getCertContents($scopeCode = null, bool $websiteScope = false): string
    {
        $cert = (string)$this->getConfig(self::XML_PATH_CERT_FILE, $scopeCode, $websiteScope);

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return $cert !== '' ? (string)base64_decode($cert) : '';
    }

    /**
     * Get certificate password.
     *
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return string
     */
    public function getCertPassword($scopeCode = null, bool $websiteScope = false): string
    {
        return (string)$this->getConfig(self::XML_PATH_CERT_PASSWORD, $scopeCode, $websiteScope);
    }

    /**
     * Get payment method codes the EET receipt is sent for.
     *
     * @param int|string|null $scopeCode
     * @return string[]
     */
    public function getPaymentMethods($scopeCode = null): array
    {
        $methods = (string)$this->getConfig(self::XML_PATH_PAYMENT_METHODS, $scopeCode);

        return $methods !== '' ? explode(',', $methods) : [];
    }

    /**
     * Get active payment method codes for the scope of the current admin config request.
     *
     * @return string[]
     */
    public function getActivePaymentMethods(): array
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $code = null;

        $website = $this->_getRequest()->getParam('website');
        $store = $this->_getRequest()->getParam('store');

        if ($website !== null) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
            $code = (int)$website;
        } elseif ($store !== null) {
            $scope = ScopeInterface::SCOPE_STORE;
            $code = (int)$store;
        }

        $methods = [];
        foreach ((array)$this->scopeConfig->getValue('payment', $scope, $code) as $methodCode => $data) {
            if (isset($data['active'], $data['model']) && (bool)$data['active']) {
                $methods[] = (string)$methodCode;
            }
        }

        return $methods;
    }

    /**
     * Read a config value in store or website scope.
     *
     * @param string $path
     * @param int|string|null $scopeCode
     * @param bool $websiteScope
     * @return mixed
     */
    private function getConfig(string $path, $scopeCode = null, bool $websiteScope = false)
    {
        $scope = $websiteScope ? ScopeInterface::SCOPE_WEBSITE : ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $scope, $scopeCode);
    }
}
