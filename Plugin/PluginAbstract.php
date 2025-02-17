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

namespace HelloMage\DeleteCreditmemo\Plugin;

use HelloMage\DeleteCreditmemo\Helper\Config as SystemConfig;
use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Backend\Model\Auth\Session;

/**
 * Class PluginAbstract
 * @package HelloMage\DeleteCreditmemo\Plugin
 */
class PluginAbstract
{
    /**
     * @var AclRetriever
     */
    protected $aclRetriever;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var SystemConfig
     */
    protected $systemConfig;

    /**
     * PluginAbstract constructor.
     * @param AclRetriever $aclRetriever
     * @param Session $authSession
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        AclRetriever $aclRetriever,
        Session $authSession,
        SystemConfig $systemConfig
    ) {
        $this->aclRetriever = $aclRetriever;
        $this->authSession = $authSession;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAllowedResources()
    {
        $user = $this->authSession->getUser();
        $role = $user->getRole();
        $resources = $this->aclRetriever->getAllowedResourcesByRole($role->getId());

        if ($this->systemConfig->IsEnabled()) {
            if (in_array("Magento_Backend::all", $resources) || in_array("HelloMage_DeleteCreditmemo::delete", $resources)) {
                return true;
            }
        }

        return false;
    }
}
