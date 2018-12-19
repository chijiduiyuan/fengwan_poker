<?php
/**
 * paypal支付
 *
 * @author   HJH
 * @version  2017-8-3
 */

require 'autoload.php';

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Payment;


function getPayment($paymentId) {

	//sandbox
	// $clientId = 'AfxCAcpShH5b20smZmZyH6pFAWdlPYBlgoP-S69Yj_0-vCcvZbP3xl45q659QH2bJPhSREInh-2grru3';
	// $clientSecret = 'EO1T_ifWMoUDZGa_cUwmqJG418CbhfPU2csEdWgQgW8pFwrncNFRZFEoMVFel0W_2LCk8RN2yzBYMWS6';

	//live
	$clientId = 'AWe9_WjKV-zoibKy3-58JEkS06jpt4aNN45A79x2_nd4nsluit_j0kALJsjhUMU4CF29cXO_zLeTN2Wg';
	$clientSecret = 'EAXkAC9xQLohnFOWJrwKqGqb-BMCeWc-cLKx_ibjC69XohjqWjUlFsMDGZ11vnnLAUhbhsTYkP5g8Spa';


	$apiContext = new ApiContext(
	        new OAuthTokenCredential(
	            $clientId,
	            $clientSecret
	        )
	    );

	$apiContext->setConfig(
	        array(
	        	// 'mode' => 'sandbox',
	            'mode' => 'live',
	            'log.LogEnabled' => false,
	            'log.FileName' => '../PayPal.log',
	            'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
	            'cache.enabled' => false,
	            // 'http.CURLOPT_CONNECTTIMEOUT' => 30
	            // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
	            //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
	        )
	    );


	return Payment::get($paymentId, $apiContext);
}