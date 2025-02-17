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

namespace HelloMage\DeleteCreditmemo\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Class Data
 * @package HelloMage\DeleteCreditmemo\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected DeploymentConfig $deploymentConfig;

    protected ResourceConnection $resourceConnection;

    /**
     * Data constructor.
     * @param Context $context
     * @param DeploymentConfig $deploymentConfig
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        DeploymentConfig $deploymentConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->deploymentConfig = $deploymentConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get Table name using direct query
     */
    public function getConnection()
    {
        /* Create Connection */
        return $this->resourceConnection->getConnection();
    }

    /**
     * @param null $name
     * @return bool|string|null
     * @throws FileSystemException
     * @throws RuntimeException
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

        $connection = $this->getConnection();
        return $connection->getTableName($tableName);
    }
}
