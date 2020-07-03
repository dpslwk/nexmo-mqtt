<?php

return [
    'settings' => [
        'nexmoApiKey' => $_ENV['NEXMO_API_KEY'] ?? '',
        'mqttHost' => $_ENV['MQTT_HOST'] ?? '127.0.0.1',
        'mqttPort' => (int) ($_ENV['MQTT_PORT'] ?? 1883),
        'mqttTopic' => $_ENV['MQTT_TOPIC'] ?? 'nexmo/inbound',
    ]
];