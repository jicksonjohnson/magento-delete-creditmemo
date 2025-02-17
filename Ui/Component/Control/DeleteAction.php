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

namespace HelloMage\DeleteCreditmemo\Ui\Component\Control;

use Magento\Ui\Component\Control\Action;

/**
 * Class DeleteAction
 * @package HelloMage\DeleteCreditmemo\Ui\Component\Control
 */
class DeleteAction extends Action
{
    /**
     * Creditmemo delete button URL
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        $context = $this->getContext();

        $config['url'] = $context->getUrl(
            $config['deleteAction'],
            ['creditmemo_id' => $context->getRequestParam('creditmemo_id')]
        );

        $this->setData('config', (array)$config);

        parent::prepare();
    }
}
