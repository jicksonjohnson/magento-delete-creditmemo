<?php
/**
 * HelloMage
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customise this module for your needs.
 * Please contact us info@hellomage.com
 *
 * @category   HelloMage
 * @package    HelloMage_DeleteCreditmemo
 * @copyright  Copyright (C) 2020 HELLOMAGE PVT LTD (https://www.hellomage.com/)
 * @license    https://www.hellomage.com/magento2-osl-3-0-license/
 */

namespace HelloMage\DeleteCreditmemo\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package HelloMage\DeleteCreditmemo\Helper\Config
 */
class Config
{
    const XML_PATH_IS_ENABLED     = 'hm-delete-credit-memo/general/is_enabled';
    const XML_PATH_REDIRECT_PAGE  = 'hm-delete-credit-memo/general/redirect_page';
    const XML_PATH_EMAIL_IDENTITY = 'hm-delete-credit-memo/general/identity';
    const XML_PATH_EMAIL_COPY_TO  = 'hm-delete-credit-memo/general/copy_to';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    ) {
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function IsEnabled($storeId = null)
    {
        return (int)$this->getConfigValue($this::XML_PATH_IS_ENABLED, $this->getStoreId());
    }

    /**
     * @param int|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRedirectPage($storeId = null)
    {
        return (string)$this->getConfigValue($this::XML_PATH_REDIRECT_PAGE, $this->getStoreId());
    }

    /**
     * @param int|null $storeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getEmailIdentity($storeId = null)
    {
        return $this->getConfigValue($this::XML_PATH_EMAIL_IDENTITY, $this->getStoreId());
    }

    /**
     * Return email copy_to list
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getEmailCopyTo()
    {
        $data = $this->getConfigValue($this::XML_PATH_EMAIL_COPY_TO, $this->getStoreId());
        if (!empty($data)) {
            return explode(',', $data);
        }
        return null;
    }

    /**
     * Return store configuration value
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get store identifier
     *
     * @return  int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get website identifier
     *
     * @return string|int|null
     * @throws NoSuchEntityException
     */
    public function getWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }
}
