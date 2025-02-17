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

namespace HelloMage\DeleteCreditmemo\Controller\Adminhtml\Delete;

use HelloMage\DeleteCreditmemo\Helper\Config as SystemConfig;
use HelloMage\DeleteCreditmemo\Model\Creditmemo\Delete;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassCreditmemo
 * @package HelloMage\DeleteCreditmemo\Controller\Adminhtml\Delete
 */
class MassCreditmemo extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory
     */
    protected $creditmemoCollectionFactory;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var Delete
     */
    protected $delete;

    /**
     * @var SystemConfig
     */
    protected $systemConfig;

    /**
     * MassCreditmemo constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderManagementInterface $orderManagement
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param Delete $delete
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory $creditmemoCollectionFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        Delete $delete,
        SystemConfig $systemConfig
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->creditmemoCollectionFactory = $creditmemoCollectionFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->delete = $delete;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function massAction(AbstractCollection $collection)
    {
        $params = $this->getRequest()->getParams();
        $selected = [];
        $collectionCreditmemo = $this->filter->getCollection($this->creditmemoCollectionFactory->create());
        $is_enabled = $this->systemConfig->IsEnabled();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($is_enabled) {
            foreach ($collectionCreditmemo as $creditmemo) {
                array_push($selected, $creditmemo->getId());
            }

            if ($selected) {
                foreach ($selected as $creditmemoId) {
                    $creditmemo = $this->creditmemoRepository->get($creditmemoId);
                    try {
                        $order = $this->deleteCreditmemo($creditmemoId);

                        $this->messageManager->addSuccessMessage(__('Successfully deleted credit memo #%1.', $creditmemo->getIncrementId()));
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('Error delete credit memo #%1.', $creditmemo->getIncrementId()));
                    }
                }
            }

            if ($params['namespace'] == 'sales_order_view_creditmemo_grid') {
                $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            } else {
                $resultRedirect->setPath('sales/creditmemo/');
            }
            return $resultRedirect;
        } else {
            $this->messageManager->addErrorMessage(__('You are not authorized to delete or delete feature disabled. please check the ACL and HelloMage Delete Credit-memo module settings'));
            $resultRedirect->setPath('sales/creditmemo/');
            return $resultRedirect;
        }
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('HelloMage_DeleteCreditmemo::massDelete');
    }

    /**
     * @param $creditmemoId
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    protected function deleteCreditmemo($creditmemoId)
    {
        return $this->delete->deleteCreditmemo($creditmemoId);
    }
}
