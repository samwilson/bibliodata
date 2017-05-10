<?php

namespace Samwilson\Bibliodata\Controllers;

use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;
use MediaWiki\OAuthClient\Consumer;
use MediaWiki\OAuthClient\Exception;
use MediaWiki\OAuthClient\Token;
use Samwilson\Bibliodata\WdWpOauth;

class OptionsController extends ControllerBase {

	public function index() {
		$wdWpOauth = new WdWpOauth();
		$apiKey = $wdWpOauth->getApiKey();
		$apiSecret = $wdWpOauth->getApiSecret();
		$oauthIdentity = $wdWpOauth->getIdentity();
		require_once WP_PLUGIN_DIR.'/bibliodata/templates/options.html.php';
	}

	public function save() {
		if (current_user_can('manage_options')) {
			if (!empty($_POST['api_key'])) {
				update_option('bibliodata_api_key', $_POST['api_key']);
			}
			if (!empty($_POST['api_secret'])) {
				update_option('bibliodata_api_secret', $_POST['api_secret']);
			}
		}
		wp_redirect('options-general.php?page=bibliodata-options');
		exit;
	}

	public function oauth() {
		$wdWpOauth = new WdWpOauth();
		$client = $wdWpOauth->getOauthClient();
		try {
			list( $next, $token ) = $client->initiate();
		}catch (Exception $oauthException) {
			echo "<p class='error'>".$oauthException->getMessage()."</p>";
			return;
		}
		set_transient('bibliodata_oauth_request_token', $token);
		wp_redirect($next);
		exit;
	}

	public function oauth_callback() {
		if (!isset($_GET['oauth_verifier'])) {
			return;
		}
		/** @var Token $token */
		$token = get_transient('bibliodata_oauth_request_token');
		$verifier = $_GET['oauth_verifier'];

		$wdWpOauth = new WdWpOauth();
		$client = $wdWpOauth->getOauthClient();
		$accessToken = $client->complete( $token,  $verifier );

		// Store the access token. @TODO move this to WdWpOauth.
		update_option('bibliodata_oauth_'.wp_get_current_user()->ID, $accessToken);

		wp_redirect('options-general.php?page=bibliodata-options');
		exit;
	}

	public function logout() {
		$wdWpOauth = new WdWpOauth();
		$wdWpOauth->logout();
		wp_redirect('options-general.php?page=bibliodata-options');
		exit;
	}
}
