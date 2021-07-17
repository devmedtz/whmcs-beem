<?php
/**
 * WHMCS Sample Payment Gateway Module
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

//  Define module related meta data.

function beem_MetaData()
{
    return array(
        'DisplayName' => 'BEEM PAYMENT',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}


// Define gateway configuration options.

function beem_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'BEEM PAYMENT',
        ),
        // Text Field for BEEM API KEY
        'apiKey' => array(
            'FriendlyName' => 'API KEY',
            'Type' => 'text',
            'Size' => '50',
            'Description' => 'Enter your account API KEY here',
        ),
        // Text Field for BEEM SECRET KEY
        'secretKey' => array(
            'FriendlyName' => 'SECRET KEY',
            'Type' => 'text',
            'Size' => '50',
            'Description' => 'Enter secret key here',
        ),
        // Text Field for BEEM KEYWORD
        'keyword' => array(
            'FriendlyName' => 'KEYWORD',
            'Type' => 'text',
            'Size' => '50',
            'Description' => 'Enter your beem keyword here eg. SAMPLE',
        ),
    );
}

// Generate UUID 

function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


// Payment link.

function beem_link($params)
{
    // Configuration Parameters
    $apiKey = $params['apiKey'];
    $secretKey = $params['secretKey'];

    //Parameters
    $invoiceId = $params['invoiceid'];
    $amount = intval($params['amount']);
    $mobile = $params['clientdetails']['phonenumber'];
    $phone = '255'.strval($mobile);
    $reference_number = "TECH" ."-". $invoiceId;
    $transaction_id = guidv4();
    
    $url ="https://checkout.beem.africa/v1/checkout?amount=$amount&reference_number=$reference_number&sendSource=true&transaction_id=$transaction_id&mobile=$phone";
    
    // Setup cURL
    $ch = curl_init($url);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt_array($ch, array(
        CURLOPT_HTTPGET => TRUE,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HTTPHEADER => array(
            'Authorization:Basic ' . base64_encode("$apiKey:$secretKey"),
            'Content-Type: application/json'
        ),
    ));
    
    // Send the request
    $response = curl_exec($ch);
    $results = json_decode($response);
    $redirect_url = $results->src;

    $code = '<a class="btn btn-primary my-2 py-2 mx-3" href="'.$redirect_url.'" target="_blank">Pay Now</a>';
    return $code;
}