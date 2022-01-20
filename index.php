<?php

require_once('./vendor/autoload.php');

use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response\SapiEmitter;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Create a PSR7 request based on the current browser request.
$request = ServerRequestFactory::fromGlobals();

// Create a guzzle client
$guzzle = new GuzzleHttp\Client();

// Create the proxy instance
$proxy = new Proxy(new GuzzleAdapter($guzzle));

// Add a response filter that removes the encoding headers.
$proxy->filter(new RemoveEncodingFilter());

try {
    // Forward the request and get the response.
    $response = $proxy->forward($request)->to($_ENV['TARGET_HOST']);

    // Output response to the browser.
    (new SapiEmitter)->emit($response);
} catch (\GuzzleHttp\Exception\BadResponseException $e) {
    // Correct way to handle bad responses
    (new SapiEmitter)->emit($e->getResponse());
}
