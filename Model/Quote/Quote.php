<?php

namespace IWD\CheckoutConnector\Model\Quote;

use Exception;
use Magento\Quote\Model\QuoteRepository;
use IWD\CheckoutConnector\Plugin\QuoteRepository\AccessChangeQuoteControl;

/**
 * Class Quote
 * @package IWD\CheckoutConnector\Model\Quote
 */
class Quote
{
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var AccessChangeQuoteControl
     */
    private $accessChangeQuoteControl;

    /**
     * Quote constructor.
     *
     * @param QuoteRepository $quoteRepository
     * @param AccessChangeQuoteControl $accessChangeQuoteControl
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        AccessChangeQuoteControl $accessChangeQuoteControl
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->accessChangeQuoteControl = $accessChangeQuoteControl;
    }

    /**
     * @param $quote_id
     * @return \Magento\Quote\Model\Quote
     * @throws Exception
     */
    public function getQuote($quote_id)
    {
        $quote = $this->quoteRepository->get($quote_id);

        if (!$quote->getId()) {
            throw new Exception('Quote ID is invalid.');
        }

        // Fix possible "Invalid State Change Requested" issue.
        $this->accessChangeQuoteControl->setForceAllowed(true);

        return $quote;
    }
}
