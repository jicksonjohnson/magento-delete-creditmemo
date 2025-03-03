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

namespace HelloMage\DeleteCreditmemo\Controller\Adminhtml\Delete;

use HelloMage\DeleteCreditmemo\Helper\Config as SystemConfig;
use HelloMage\DeleteCreditmemo\Model\Creditmemo\Delete;

use Exception;
use Magento\Backend\App\Action;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

/**
 * Class Creditmemo
 * @package HelloMage\DeleteCreditmemo\Controller\Adminhtml\Delete
 */
class Creditmemo extends Action
{
    protected CreditmemoRepositoryInterface $creditmemoRepository;

    protected Delete $delete;
    protected SystemConfig $systemConfig;

    /**
     * Creditmemo constructor.
     * @param Action\Context $context
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param Delete $delete
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        Action\Context $context,
        CreditmemoRepositoryInterface $creditmemoRepository,
        Delete $delete,
        SystemConfig $systemConfig
    ) {
        $this->creditmemoRepository = $creditmemoRepository;
        $this->delete = $delete;
        $this->systemConfig = $systemConfig;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        $orderId = $creditmemo->getOrderId();
        $redirect_page = $this->systemConfig->getRedirectPage();
        $is_enabled = $this->systemConfig->IsEnabled();
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($is_enabled) {
            try {
                $this->delete->deleteCreditmemo($creditmemoId);
                $this->messageManager->addSuccessMessage(__('Successfully deleted credit-memo #%1.', $creditmemo->getIncrementId()));
                if ($redirect_page == 'order-view') {
                    // redirecting to relative order page
                    $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]); // redirecting to relative order page
                } elseif ($redirect_page == 'credit-memo-listing') {
                    // redirecting to creditmemo listing
                    $resultRedirect->setPath('sales/creditmemo/');
                } else {
                    // redirecting to order listing
                    $resultRedirect->setPath('sales/order');
                }
                return $resultRedirect;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Error delete credit-memo #%1.', $creditmemo->getIncrementId()));
                // redirecting to creditmemo view
                $resultRedirect->setPath('sales/creditmemo/view', ['creditmemo_id' => $creditmemoId]);
                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('You are not authorized to delete or delete feature disabled. please check the ACL and HelloMage Delete Credit-memo module settings'));
            // redirecting to creditmemo listing
            $resultRedirect->setPath('sales/creditmemo/view', ['creditmemo_id' => $creditmemoId]);
            return $resultRedirect;
        }
    }

    /*
     * Check permission via ACL resource
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('HelloMage_DeleteCreditmemo::delete');
    }
}
