<?php
/**
 * HelloMage
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customise this module for your needs.
 * Please contact us jicksonkoottala@gmail.com
 *
 * @category   HelloMage
 * @package    HelloMage_DeleteCreditmemo
 * @copyright  Copyright (C) 2020 HELLOMAGE PVT LTD (https://www.hellomage.com/)
 * @license    https://www.hellomage.com/magento2-osl-3-0-license/
 */

declare(strict_types=1);

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
    protected AclRetriever $aclRetriever;

    protected Session $authSession;

    /**
     * PluginAbstract constructor.
     * @param AclRetriever $aclRetriever
     * @param Session $authSession
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        AclRetriever $aclRetriever,
        Session $authSession
    ) {
        $this->aclRetriever = $aclRetriever;
        $this->authSession = $authSession;
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

        if (in_array("Magento_Backend::all", $resources) || in_array("HelloMage_DeleteCreditmemo::delete", $resources)) {
            return true;
        }

        return false;
    }
}
