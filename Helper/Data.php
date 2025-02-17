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

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Config\ConfigOptionsListConstants;

/**
 * Class Data
 * @package HelloMage\DeleteCreditmemo\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Data constructor.
     * @param Context $context
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Context $context,
        DeploymentConfig $deploymentConfig
    ) {
        parent::__construct($context);
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @param null $name
     * @return bool|string|null
     */
    public function getTableName($name = null)
    {
        if ($name == null) {
            return false;
        }

        $tableName = $name;
        $tablePrefix = (string)$this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
        );

        if ($tablePrefix) {
            $tableName = $tablePrefix . $name;
        }

        return $tableName;
    }
}
