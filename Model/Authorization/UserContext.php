<?php

namespace IWD\CheckoutConnector\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Webapi\Model\Authorization\TokenUserContext;

/**
 * Class UserContext
 *
 * @package IWD\CheckoutConnector\Model\Authorization
 */
class UserContext extends TokenUserContext
{
    /**
     * @return int|null
     */
    public function getUserType()
    {
        $this->processRequest();
        return $this->isCheckoutPath() ? UserContextInterface::USER_TYPE_INTEGRATION : $this->userType;
    }

    /**
     * @return bool
     */
    protected function isCheckoutPath()
    {
        return strpos($this->request->getPathInfo(), 'iwd-checkout') !== false;
    }
}
