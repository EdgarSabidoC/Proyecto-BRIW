<?php
//funciono XD
require_once('vendor/autoload.php');

// check Solarium version available
echo 'Solarium library version: ' . Solarium\Client::getVersion() . ' - ';

// create a client instance
$client = new Solarium\Client($adapter, $eventDispatcher, $config);

// create a ping query
$ping = $client->createPing();

// execute the ping query
try {
    $result = $client->ping($ping);
    echo 'Ping query successful';
    echo '<br/><pre>';
    var_dump($result->getData());
    echo '</pre>';
} catch (Exception $e) {
    echo 'Ping query failed';
}

?>