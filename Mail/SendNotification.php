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

namespace HelloMage\DeleteCreditmemo\Mail;

use Exception;

use HelloMage\DeleteCreditmemo\Helper\Config as SystemConfig;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SendNotification
 * @package HelloMage\DeleteCreditmemo\Helper
 */
class SendNotification extends AbstractHelper
{
    protected TransportBuilder $transportBuilder;

    protected StoreManagerInterface $storeManager;

    protected StateInterface $inlineTranslation;

    protected SenderResolverInterface $senderResolver;

    protected SystemConfig $systemConfig;

    /**
     * SendNotification constructor.
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $state
     * @param SenderResolverInterface $senderResolver
     * @param SystemConfig $systemConfig
     */
    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        SenderResolverInterface $senderResolver,
        SystemConfig $systemConfig
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->senderResolver = $senderResolver;
        $this->systemConfig = $systemConfig;
        parent::__construct($context);
    }

    /**
     * @param $data
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendEmail($data)
    {
        $emailIdentity = $this->senderResolver->resolve($this->systemConfig->getEmailIdentity());

        // this is an example and you can change template id,fromEmail,toEmail,etc as per your need.
        $templateId = 'creditmemo_delete_developer_notification'; // template id
        $fromEmail = $emailIdentity['email'];  // sender Email id
        $fromName = $emailIdentity['name']; // sender Name
        $toEmails = $this->systemConfig->getEmailCopyTo(); // receiver email id

        if (isset($toEmails)) {
            try {
                // template variables pass here
                $templateVars = [
                    'creditmemo_id' => isset($data['creditmemo_id']) ? $data['creditmemo_id'] : ' NO DATA FOUND ',
                    'increment_id' => isset($data['increment_id']) ? $data['increment_id'] : ' NO DATA FOUND ',
                    'order_id' => isset($data['order_id']) ? $data['order_id'] : ' NO DATA FOUND ',
                    'admin_details' => isset($data['admin_details']) ? $data['admin_details'] : ' NO DATA FOUND ',
                    'deleted_at' => isset($data['deleted_at']) ? $data['deleted_at'] : ' NO DATA FOUND '
                ];

                $storeId = $data['store_id'];
                $from = ['email' => $fromEmail, 'name' => $fromName];
                $this->inlineTranslation->suspend();
                $storeScope = ScopeInterface::SCOPE_STORE;
                $templateOptions = [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ];

                foreach ($toEmails as $toEmail) {
                    $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($toEmail)
                        ->getTransport();
                    $transport->sendMessage();
                }

                $this->inlineTranslation->resume();
            } catch (Exception $e) {
                $this->_logger->info($e->getMessage());
            }
        }
    }
}
