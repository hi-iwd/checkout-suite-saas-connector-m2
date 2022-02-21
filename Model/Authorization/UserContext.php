<?php

namespace IWD\CheckoutConnector\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Class UserContext
 *
 * @package IWD\CheckoutConnector\Model\Authorization
 */
class UserContext implements UserContextInterface
{
    /**
     * @return int|null
     */
    public function getUserId()
    {
        return 0;
    }

    /**
     * @return int|null
     */
    public function getUserType()
    {
        return UserContextInterface::USER_TYPE_INTEGRATION;
    }
}
