<?php

namespace IWD\CheckoutConnector\Plugin\QuoteRepository;

use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class AccessChangeQuoteControl
 *
 * @package IWD\CheckoutConnector\Plugin\QuoteRepository
 */
class AccessChangeQuoteControl extends \Magento\Quote\Model\QuoteRepository\Plugin\AccessChangeQuoteControl
{
    /**
     * @var bool
     */
    private $forceAllowed = false;

    /**
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return void
     * @throws StateException
     */
    public function beforeSave(CartRepositoryInterface $subject, CartInterface $quote) :void
    {
        if(!$this->forceAllowed) {
            parent::beforeSave($subject, $quote);
        }
    }

    /**
     * @param $val
     */
    public function setForceAllowed($val)
    {
        $this->forceAllowed = $val;
    }
}