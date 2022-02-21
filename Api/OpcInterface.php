<?php
namespace IWD\CheckoutConnector\Api;

 /**
  * Interface OpcInterface
  *
  * @package IWD\CheckoutConnector\Api
  */
 interface OpcInterface
 {
     /**
      * @api
      * @param string $quote_id
      * @param mixed $access_tokens
      * @return mixed[]|string
      */
     public function getData($quote_id, $access_tokens);
 }
