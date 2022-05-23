<?php
declare(strict_types=1);

namespace CakeSlack;

use Cake\Http\Client;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Client\Response;

class Slack
{
    use InstanceConfigTrait;

    /**
     * @var \Cake\Http\Client
     */
    protected $_client = null;

    /**
     * Default configuration for the client.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'channel' => '#general',
        'username' => 'CakeSlack',
        'icon_emoji' => ':ghost:',
    ];

    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    protected  function getClient() {
        if ($this->_client === null) {
            $this->_client = new Client();
        }

        return $this->_client;
    }

    /**
     * Send message to slack
     *
     * @see https://api.slack.com/docs/message-attachments
     *
     * @param $message string|array
     * @return bool | Cake\Http\Client\Response
     */
    public function send($message, array $params = []): bool | Response
    {
        $settings = $params + $this->getConfig();
        $client = $this->getClient();
        if (is_array($message)) {
            $payload = [
                'channel' => $settings['channel'],
                'username' => $settings['username'],
                'icon_emoji' => $settings['icon_emoji'],
                'attachments' => $message,
            ];
        } else {
            $payload = [
                'channel' => $settings['channel'],
                'username' => $settings['username'],
                'icon_emoji' => $settings['icon_emoji'],
                'text' => $message,
            ];
        }

        $token = $this->getConfig('token');
        if (empty($token)) {
            return false;
        }
        $uri = "https://hooks.slack.com/services/{$token}";
        $request = [
            'header' => [
                'Content-Type' => 'application/json',
            ]
        ];

        $response = $client->post($uri, json_encode($payload), $request);
        if (!$response->isSuccess() || $response->getStringBody() !== 'ok') {
            return $response;
        }

        return true;
    }
}
