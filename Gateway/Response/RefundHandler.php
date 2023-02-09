<?php

namespace IWD\CheckoutConnector\Gateway\Response;

use Exception;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoCommentSender;

/**
 * Class RefundHandler
 *
 * @package IWD\CheckoutConnector\Gateway\Response
 */
class RefundHandler implements HandlerInterface
{
    const TRANSACTION_ID = 'transaction_id';
	const COMMENT = 'comment';

	/**
	 * @var CreditmemoSender
	 */
	private $creditmemoSender;

	/**
	 * @var CreditmemoCommentSender
	 */
	private $creditmemoCommentSender;

	/**
	 * @param  CreditmemoSender  $creditmemoSender
	 * @param  CreditmemoCommentSender  $creditmemoCommentSender
	 */
	public function __construct(
		CreditmemoSender $creditmemoSender,
		CreditmemoCommentSender $creditmemoCommentSender
	) {
		$this->creditmemoSender = $creditmemoSender;
		$this->creditmemoCommentSender = $creditmemoCommentSender;
	}

	/**
	 * @param  array  $handlingSubject
	 * @param  array  $response
	 *
	 * @throws Exception
	 */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $payment->setTransactionId($response[self::TRANSACTION_ID]);
        $payment->setIsTransactionClosed(true);

		/** @var $creditmemo Creditmemo */
	    $creditmemo = $payment->getCreditmemo();
        $closed = $this->needCloseParentTransaction($creditmemo);
        $payment->setShouldCloseParentTransaction($closed);

	    $this->creditmemoSender->send($creditmemo);

		if (isset($response[self::COMMENT])) {
			$order = $creditmemo->getOrder();
			$order->addCommentToStatusHistory($response[self::COMMENT], false, true);
			$order->save();

			$creditmemo->addComment($response[self::COMMENT], true, true);
			$creditmemo->save();

			$this->creditmemoCommentSender->send($creditmemo, true, $response[self::COMMENT]);
		}
    }

    /**
     * @param $creditmemo Creditmemo
     * @return bool
     */
    private function needCloseParentTransaction($creditmemo)
    {
        return !(bool)$creditmemo->getInvoice()->canRefund();
    }
}
