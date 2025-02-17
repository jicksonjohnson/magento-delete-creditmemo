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

namespace HelloMage\DeleteCreditmemo\Model\Creditmemo;

use Exception;
use HelloMage\DeleteCreditmemo\Helper\Data;
use HelloMage\DeleteCreditmemo\Mail\SendNotification;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 * @package HelloMage\DeleteCreditmemo\Model\Creditmemo
 */
class Delete
{
    protected ResourceConnection $resource;

    /**
     * @var
     */
    protected Data $data;

    protected CreditmemoRepositoryInterface $creditmemoRepository;

    protected Order $order;

    private LoggerInterface $logger;

    protected SendNotification $sendNotification;

    protected Session $_authSession;

    /**
     * Delete constructor.
     * @param ResourceConnection $resource
     * @param Data $data
     * @param Order $order
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param LoggerInterface $logger
     * @param SendNotification $sendNotification
     * @param Session $authSession
     */
    public function __construct(
        ResourceConnection $resource,
        Data $data,
        Order $order,
        CreditmemoRepositoryInterface $creditmemoRepository,
        LoggerInterface $logger,
        SendNotification $sendNotification,
        Session $authSession
    ) {
        $this->resource = $resource;
        $this->data = $data;
        $this->order = $order;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->logger = $logger;
        $this->sendNotification = $sendNotification;
        $this->_authSession = $authSession;
    }

    /**
     * @param $creditmemoId
     * @return \Magento\Sales\Model\Order
     * @throws \Exception
     */
    public function deleteCreditmemo($creditmemoId)
    {
        $connection = $this->data->getConnection();
        $creditmemoGridTable = $this->data->getTableName('sales_creditmemo_grid');
        $creditmemoTable = $this->data->getTableName('sales_creditmemo');

        $creditmemo = $this->creditmemoRepository->get($creditmemoId);
        $orderId = $creditmemo->getOrder()->getId();
        $order = $this->order->load($orderId);
        $orderItems = $order->getAllItems();
        $creditmemoItems = $creditmemo->getAllItems();

        // revert credit memo fields in ordered items table
        foreach ($orderItems as $item) {
            foreach ($creditmemoItems as $creditmemoItem) {
                if ($creditmemoItem->getOrderItemId() == $item->getItemId()) {
                    $item->setQtyRefunded($item->getQtyRefunded() - $creditmemoItem->getQty());
                    $item->setTaxRefunded($item->getTaxRefunded() - $creditmemoItem->getTaxAmount());
                    $item->setBaseTaxRefunded($item->getBaseTaxRefunded() - $creditmemoItem->getBaseTaxAmount());
                    $discountTaxItem = $item->getDiscountTaxCompensationRefunded();
                    $discountTaxCredit = $creditmemoItem->getDiscountTaxCompensationAmount();
                    $item->setDiscountTaxCompensationRefunded(
                        $discountTaxItem - $discountTaxCredit
                    );
                    $baseDiscountItem = $item->getBaseDiscountTaxCompensationRefunded();
                    $baseDiscountCredit = $creditmemoItem->getBaseDiscountTaxCompensationAmount();
                    $item->setBaseDiscountTaxCompensationRefunded(
                        $baseDiscountItem - $baseDiscountCredit
                    );
                    $item->setAmountRefunded($item->getAmountRefunded() - $creditmemoItem->getRowTotal());
                    $item->setBaseAmountRefunded($item->getBaseAmountRefunded() - $creditmemoItem->getBaseRowTotal());
                    $item->setDiscountRefunded($item->getDiscountRefunded() - $creditmemoItem->getDiscountAmount());
                    $item->setBaseDiscountRefunded(
                        $item->getBaseDiscountRefunded() - $creditmemoItem->getBaseDiscountAmount()
                    );
                }
            }
        }

        // revert info in order table
        $order->setBaseTotalRefunded($order->getBaseTotalRefunded() - $creditmemo->getBaseGrandTotal());
        $order->setTotalRefunded($order->getTotalRefunded() - $creditmemo->getGrandTotal());

        $order->setBaseSubtotalRefunded($order->getBaseSubtotalRefunded() - $creditmemo->getBaseSubtotal());
        $order->setSubtotalRefunded($order->getSubtotalRefunded() - $creditmemo->getSubtotal());

        $order->setBaseTaxRefunded($order->getBaseTaxRefunded() - $creditmemo->getBaseTaxAmount());
        $order->setTaxRefunded($order->getTaxRefunded() - $creditmemo->getTaxAmount());
        $order->setBaseDiscountTaxCompensationRefunded(
            $order->getBaseDiscountTaxCompensationRefunded() - $creditmemo->getBaseDiscountTaxCompensationAmount()
        );
        $order->setDiscountTaxCompensationRefunded(
            $order->getDiscountTaxCompensationRefunded() - $creditmemo->getDiscountTaxCompensationAmount()
        );

        $order->setBaseShippingRefunded($order->getBaseShippingRefunded() - $creditmemo->getBaseShippingAmount());
        $order->setShippingRefunded($order->getShippingRefunded() - $creditmemo->getShippingAmount());

        $order->setBaseShippingTaxRefunded(
            $order->getBaseShippingTaxRefunded() - $creditmemo->getBaseShippingTaxAmount()
        );
        $order->setShippingTaxRefunded($order->getShippingTaxRefunded() - $creditmemo->getShippingTaxAmount());
        $order->setAdjustmentPositive($order->getAdjustmentPositive() - $creditmemo->getAdjustmentPositive());
        $order->setBaseAdjustmentPositive(
            $order->getBaseAdjustmentPositive() - $creditmemo->getBaseAdjustmentPositive()
        );
        $order->setAdjustmentNegative($order->getAdjustmentNegative() - $creditmemo->getAdjustmentNegative());
        $order->setBaseAdjustmentNegative(
            $order->getBaseAdjustmentNegative() - $creditmemo->getBaseAdjustmentNegative()
        );
        $order->setDiscountRefunded($order->getDiscountRefunded() - $creditmemo->getDiscountAmount());
        $order->setBaseDiscountRefunded($order->getBaseDiscountRefunded() - $creditmemo->getBaseDiscountAmount());

        $this->setTotalandBaseTotal($creditmemo, $order);

        // delete creditmemo info
        $connection->rawQuery('DELETE FROM `'.$creditmemoGridTable.'` WHERE entity_id='.$creditmemoId);
        $connection->rawQuery('DELETE FROM `'.$creditmemoTable.'` WHERE entity_id='.$creditmemoId);

        try {
            $creditmemoData = $this->creditmemoRepository->get($creditmemoId);
            $data_to_send = [
                'creditmemo_id' => $creditmemoData->getEntityId(),
                'order_id' => $creditmemoData->getOrderId(),
                'increment_id' => $creditmemoData->getIncrementId(),
                'admin_details' => 'ID : ' . $this->_authSession->getUser()->getId() . ' | EMAIL : ' . $this->_authSession->getUser()->getEmail(),
                'deleted_at' => date("Y-m-d h:i:s"),
                'store_id' => $creditmemoData->getStoreId()
            ];

            //delete credit-memo by credit-memo object
            $this->creditmemoRepository->delete($creditmemoData);
            $this->saveOrder($order);

            $this->sendNotification->sendEmail($data_to_send);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $order;
    }

    /**
     * @param $creditmemo
     * @param $order
     */
    protected function setTotalandBaseTotal($creditmemo, $order)
    {
        if ($creditmemo->getDoTransaction()) {
            $order->setTotalOnlineRefunded($order->getTotalOnlineRefunded() - $creditmemo->getGrandTotal());
            $order->setBaseTotalOnlineRefunded($order->getBaseTotalOnlineRefunded() - $creditmemo->getBaseGrandTotal());
        } else {
            $order->setTotalOfflineRefunded($order->getTotalOfflineRefunded() - $creditmemo->getGrandTotal());
            $order->setBaseTotalOfflineRefunded(
                $order->getBaseTotalOfflineRefunded() - $creditmemo->getBaseGrandTotal()
            );
        }
    }

    /**
     * @param $order
     */
    protected function saveOrder($order)
    {
        if ($order->hasShipments() || $order->hasInvoices() || $order->hasCreditmemos()) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
                ->save();
        } elseif (!$order->canInvoice() && !$order->canShip() && !$order->hasCreditmemos()) {
            $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)
                ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_COMPLETE))
                ->save();
        } else {
            $order->setState(\Magento\Sales\Model\Order::STATE_NEW)
                ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW))
                ->save();
        }
    }
}
