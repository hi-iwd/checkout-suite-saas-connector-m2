<?php

namespace IWD\CheckoutConnector\Model\Method;

use IWD\CheckoutConnector\Helper\PaymentMethod;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Payment\Gateway\Command\CommandManagerInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Payment\Gateway\Validator\ValidatorPoolInterface;
use Psr\Log\LoggerInterface;
use Magento\Payment\Model\Method\Adapter;

/**
 * Class IWDCheckoutPay
 *
 * @package IWD\CheckoutConnector\Model\Method
 */
class IWDCheckoutPay extends Adapter
{
    private $paymentHelper;

    /**
     * @var ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var ValidatorPoolInterface
     */
    private $validatorPool;

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var string
     */
    private $formBlockType;

    /**
     * @var string
     */
    private $infoBlockType;

    /**
     * @var string
     */
    private $code;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface
     */
    private $commandExecutor;

    /**
     * Logger for exception details
     *
     * @var LoggerInterface
     */
    private $logger;

    
    public function __construct(
        ManagerInterface $eventManager,
        ValueHandlerPoolInterface $valueHandlerPool,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $code,
        $formBlockType,
        $infoBlockType,
        PaymentMethod $paymentHelper,
        CommandPoolInterface $commandPool = null,
        ValidatorPoolInterface $validatorPool = null,
        CommandManagerInterface $commandExecutor = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $eventManager,
            $valueHandlerPool,
            $paymentDataObjectFactory,
            $code,
            $formBlockType,
            $infoBlockType,
            $commandPool,
            $validatorPool,
            $commandExecutor,
            $logger
        );

        $this->valueHandlerPool = $valueHandlerPool;
        $this->validatorPool = $validatorPool;
        $this->commandPool = $commandPool;
        $this->code = $code;
        $this->infoBlockType = $infoBlockType;
        $this->formBlockType = $formBlockType;
        $this->eventManager = $eventManager;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->commandExecutor = $commandExecutor;
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param string $field
     * @param null $storeId
     * @return bool|mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if($field == 'title' && $paymentTitle = $this->paymentHelper->updatePaymentTitle()){
            return $paymentTitle;
        }

        return parent::getConfigData($field, $storeId);
    }

    /**
     * @return bool
     */
    public function canRefundPartialPerInvoice()
    {
        return true;
    }
}
