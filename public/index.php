<?php
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;;
use karpy47\PhpMqttClient\MQTTClient;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$config = require __DIR__ . '/../config.php';
$settings = $config['settings'];

$app = AppFactory::create();

$handler = function (Request $request, Response $response, array $args) use ($settings) {
    $params = $request->getParsedBody();

    // Fall back to query parameters if needed
    if (is_null($params) || ! count($params)) {
        $params = $request->getQueryParams();
    }

    // TODO: do some validation on $params
    // https://developer.nexmo.com/api/sms?theme=dark#webhooks
    // {
    //   "msisdn":"",
    //   "to":"",
    //   "messageId":"",
    //   "text":"",
    //   "type":"text",
    //   "keyword":"",
    //   "api-key":"",
    //   "message-timestamp":""
    // }


    $client = new MQTTClient($settings['mqttHost'], $settings['mqttPort']);
    $success = $client->sendConnect('nexmomqtt');  // set your client ID
    if ($success) {
        $client->sendPublish($settings['mqttTopic'], json_encode($params));
        $client->sendDisconnect();
    }
    $client->close();

    return $response->withStatus(204);
};

/**
 * The routing middleware should be added before the ErrorMiddleware
 * Otherwise exceptions thrown from it will not be handled
 */
$app->addRoutingMiddleware();

/**
 * Add Error Handling Middleware
 *
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Parameter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log
 * which can be replaced by a callable of your choice.

 * Note: This middleware should be added last. It will not handle any exceptions/errors
 * for middleware added after it.
 */
$app->addErrorMiddleware(true, true, true);

$app->get('/webhooks/inbound-sms', $handler);
$app->post('/webhooks/inbound-sms', $handler);
$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("ok");
    return $response;
});

$app->run();
