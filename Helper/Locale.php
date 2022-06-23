<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoresConfig;

/**
 * Class Locale
 *
 * @package IWD\CheckoutConnector\Helper
 */
class Locale extends AbstractHelper
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var StoresConfig
     */
    private $storesConfig;

    /**
     * Locale constructor
     *
     * @param Context $context
     * @param Http $request
     * @param StoresConfig $storesConfig
     */
    public function __construct(
        Context $context,
        Http $request,
        StoresConfig $storesConfig
    ) {
        parent::__construct($context);

        $this->request     = $request;
        $this->storesConfig = $storesConfig;
    }

    /**
     * @return string|null
     */
    public function getBrowserLocale()
    {
        $httpAcceptLanguage = substr($this->request->getServer('HTTP_ACCEPT_LANGUAGE'), 0, 2);

        if (!$httpAcceptLanguage) {
            return null;
        }

        $acceptLocaleList = $this->getAcceptLocaleList();

        foreach ($acceptLocaleList as $acceptLocale) {
            if ($acceptLocale == $httpAcceptLanguage
                || (strpos($acceptLocale, $httpAcceptLanguage) !== false)) {
                return $acceptLocale;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    private function getAcceptLocaleList()
    {
        $acceptLocaleList = $this->storesConfig->getStoresConfigByPath('general/locale/code');

        return array_unique($acceptLocaleList);
    }
}
