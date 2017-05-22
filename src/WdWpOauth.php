<?php

namespace Samwilson\Bibliodata;

use Exception;
use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Token;

class WdWpOauth {

	/** @var Client */
	protected $client;

	/** @var string The current CSRF token. */
	protected $csrfToken;

	/**
	 * Get the API key.
	 * @return string
	 */
	public function getApiKey() {
		$apiKey = get_option('bibliodata_api_key', '');
		return $apiKey;
	}

	public function getApiSecret() {
		$apiSecret = get_option('bibliodata_api_secret', '');
		return $apiSecret;
	}

	public function getAccessToken(  ) {
		$oauthAccessToken = get_option('bibliodata_oauth_'.wp_get_current_user()->ID);
		return $oauthAccessToken;
	}

	public function logout() {
		delete_option('bibliodata_oauth_'.wp_get_current_user()->ID);
	}

	/**
	 * @return bool|\stdClass
	 */
	public function getIdentity() {
		$oauthAccessToken = $this->getAccessToken();
		$oauthIdentity = false;
		if ($oauthAccessToken instanceof Token) {
			$client = $this->getOauthClient();
			$oauthIdentity = $client->identify($oauthAccessToken);
		}
		return $oauthIdentity;
	}

	/**
	 * @return Client
	 */
	public function getOauthClient() {
		if ($this->client) {
			return $this->client;
		}
		$endpoint = 'https://www.wikidata.org/w/index.php?title=Special:OAuth';
		$conf = new ClientConfig( $endpoint );
		$consumerKey = $this->getApiKey();
		$consumerSecret = $this->getApiSecret();
		$conf->setConsumer( new Consumer( $consumerKey, $consumerSecret ) );
		$client = new Client( $conf );
		$client->setCallback( 'oob' );
		$this->client = $client;
		return $client;
	}

	/**
	 * Make a oauth-signed POST request to the Wikidata API.
	 * @param array $data
	 * @return mixed
	 * @throws Exception
	 */
	public function makeCall( $data, $needsToken = false ) {
		$data['format'] = 'json';
		if ($needsToken) {
			if (!$this->csrfToken) {
				$editToken = $this->makeCall( [ 'action' => 'query', 'meta' => 'tokens' ] );
				$this->csrfToken = $editToken->query->tokens->csrftoken;
			}
			$data['token'] = $this->csrfToken;
		}
		$this->getOauthClient()->setExtraParams($data);
		$url = 'https://www.wikidata.org/w/api.php';
		$response = $this->getOauthClient()->makeOAuthCall( $this->getAccessToken(), $url, true, $data );
		$result = json_decode($response);
		if (isset($result->error)) {
			throw new Exception($result->error->info);
		}
		return $result;
	}
}