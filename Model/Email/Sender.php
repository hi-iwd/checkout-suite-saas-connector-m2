<?php

namespace IWD\CheckoutConnector\Model\Email;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use IWD\CheckoutConnector\Helper\Data as Helper;

class Sender
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectFactory $dataObjectFactory
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder     $transportBuilder,
        DataObjectFactory    $dataObjectFactory,
        State                $appState,
        LoggerInterface      $logger,
        Helper $helper,
        TimezoneInterface $timezoneInterface
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->appState = $appState;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->timezoneInterface = $timezoneInterface;
    }

    /**
     * @param $quote
     * @param $paymentInfo
     * @param $items
     * @return void
     * @throws \Exception
     */
    public function sendEmail($quote, $paymentInfo, $items)
    {
        if(!$this->helper->isNotifyCustomer($quote->getStoreId())) return;

        $transactions = !empty($paymentInfo['transactions']) ? $paymentInfo['transactions'] : '';

        $emailTempVariables = [
            'customer_email'      => $quote->getCustomerEmail(),
            'order_total'         => number_format($quote->getBaseGrandTotal(), 2, '.', ''),
            'shipping_title'      => $quote->getShippingAddress()->getShippingDescription(),
            'shipping_price'      => number_format($quote->getShippingAddress()->getShippingAmount(), 2, '.', ''),
            'payment_title'       => $paymentInfo['payment_method_title'],
            'capture_transaction' => isset($transactions['capture']) ? $transactions['capture']['id'] : '',
            'auth_transaction'    => isset($transactions['authorization']) ? $transactions['authorization']['id'] : '',
            'quote_items'         => $items,
            'time'                => $this->timezoneInterface->date()->format('Y-m-d H:i:s')
        ];

        $email = $quote->getCustomerEmail();
        $postObject = $this->dataObjectFactory->create();
        $postObject->setData($emailTempVariables);

        $emailTemplate = $this->helper->notifyCustomerTemplate($quote->getStoreId());

        $this->appState->emulateAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML, function () {});

        try {
            $transport = $this->transportBuilder->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $quote->getStoreId()])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom('general')
                ->addTo($email)
                ->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}