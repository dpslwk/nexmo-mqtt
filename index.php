<?php
use Dotenv\Dotenv;
use karpy47\PhpMqttClient\MQTTClient;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = require 'config.php';

$app = new \Slim\App($config);

$container = $app->getContainer();
$settings = $container->get('settings');

$handler = function (Request $request, Response $response) use ($settings) {
    $params = $request->getParsedBody();

    // Fall back to query parameters if needed
    if (is_null($params) || ! count($params)) {
        $params = $request->getQueryParams();
    }

    // TODO: do some validation on $params
    // https://developer.nexmo.com/api/sms?theme=dark#webhooks


    $client = new MQTTClient($settings['mqttHost'], $settings['mqttPort']);
    $success = $client->sendConnect('nexmomqtt');  // set your client ID
    if ($success) {
        $client->sendPublish($settings['mqttTopic'], json_encode($params));
        $client->sendDisconnect();
    }
    $client->close();

    return $response->withStatus(204);
};

$app->get('/webhooks/inbound-sms', $handler);
$app->post('/webhooks/inbound-sms', $handler);

$app->run();