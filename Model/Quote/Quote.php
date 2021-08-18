<?php

namespace IWD\CheckoutConnector\Model\Quote;

use Exception;
use Magento\Quote\Model\QuoteRepository;

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
     * Quote constructor.
     *
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
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

        return $quote;
    }
}
