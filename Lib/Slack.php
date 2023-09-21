<?php
namespace CakeSlack;

use Cake\Http\Client;

class Slack
{
	/**
	 * @var \Cake\Http\Client
	 */
	protected static $_client = null;

	/**
	 * @var array
	 */
	protected static $_settings = null;

	public static function initClient($options = [])
	{
		static::$_client = new Client($options);
	}

	protected static function _getClient() {
		if (static::$_client === null) {
			static::$_client = new Client();
		}

		return static::$_client;
	}

	public static function settings($key) {
		if (static::$_settings === null) {
			$settings = [
				'channel' => '#general',
				'username' => 'cakephp',
				'icon_emoji' => ':ghost:',
			];

			$tmp = \Cake\Core\Configure::read('Slack');
			if (is_array($tmp)) {
				static::$_settings = array_merge($settings, $tmp);
			}
		}
		return static::$_settings[$key];
	}

	/**
	 * Send message to slack
	 *
	 * @see https://api.slack.com/docs/message-attachments
     *
	 * @param $message string|array
	 * @return bool
	 */
	public static function send($message)
	{
		$client = static::_getClient();
		if (is_array($message)) {
			$payload = [
				'channel' => static::settings('channel'),
				'username' => static::settings('username'),
				'icon_emoji' => static::settings('icon_emoji'),
				'attachments' => $message,
			];
		} else {
			$payload = [
				'channel' => static::settings('channel'),
				'username' => static::settings('username'),
				'text' => $message,
				'icon_emoji' => static::settings('icon_emoji'),
			];
		}

		$token = static::settings('token');
		if (empty($token)) {
			return true;
		}
		$uri = "https://hooks.slack.com/services/{$token}";
		$request = [
			'header' => [
				'Content-Type' => 'application/json',
			]
		];

		$response = $client->post($uri, json_encode($payload), $request);
		if ($response->code !== 200 || $response->body !== 'ok') {
			return false;
		}

		return true;
	}
}
