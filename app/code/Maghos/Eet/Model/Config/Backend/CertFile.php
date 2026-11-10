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

namespace Maghos\Eet\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Stores the uploaded certificate base64 encoded directly in the config value.
 */
class CertFile extends Value
{
    private const MAX_FILE_SIZE = 16384;

    /** @var RequestDataInterface */
    private RequestDataInterface $requestData;

    /** @var File */
    private File $fileDriver;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param RequestDataInterface $requestData
     * @param File $fileDriver
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        RequestDataInterface $requestData,
        File $fileDriver,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->requestData = $requestData;
        $this->fileDriver = $fileDriver;
    }

    /**
     * Read the uploaded certificate into the value, or handle deletion.
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $tmpName = (string)$this->requestData->getTmpName($this->getPath());

        if ($tmpName !== '') {
            try {
                $size = (int)($this->fileDriver->stat($tmpName)['size'] ?? 0);
                if ($size > self::MAX_FILE_SIZE) {
                    throw new LocalizedException(__('File size too large'));
                }
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $this->setValue(base64_encode($this->fileDriver->fileGetContents($tmpName)));
            } catch (FileSystemException $e) {
                throw new LocalizedException(__('%1', $e->getMessage()), $e);
            }
        } else {
            $value = $this->getValue();
            if (is_array($value) && !empty($value['delete'])) {
                $this->setValue('');
            } else {
                $this->unsValue();
            }
        }

        return parent::beforeSave();
    }
}
