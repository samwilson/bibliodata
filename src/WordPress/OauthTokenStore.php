<?php

namespace Samwilson\Bibliodata\WordPress;

use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Token\TokenInterface;

class OauthTokenStore implements TokenStorageInterface {

	/**
	 * @param $service
	 *
	 * @return string
	 */
	protected function get_transient_name( $service ) {
		return 'bibliodata-oauth-token-' . $service;
	}

	/**
	 * @param string $service
	 *
	 * @return TokenInterface
	 *
	 * @throws TokenNotFoundException
	 */
	public function retrieveAccessToken( $service ) {
		return get_transient( $this->get_transient_name( $service ) );
	}

	/**
	 * @param string         $service
	 * @param TokenInterface $token
	 *
	 * @return TokenStorageInterface
	 */
	public function storeAccessToken($service, TokenInterface $token) {
		set_transient( $this->get_transient_name($service), $token );
		return $this;
	}

	/**
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAccessToken($service) {
		return !is_null( $this->retrieveAccessToken( $service ) );
	}

	/**
	 * Delete the users token. Aka, log out.
	 *
	 * @param string $service
	 *
	 * @return TokenStorageInterface
	 */
	public function clearToken($service ) {
		delete_transient( $this->get_transient_name( $service ) );
	}

	/**
	 * Delete *ALL* user tokens. Use with care. Most of the time you will likely
	 * want to use clearToken() instead.
	 *
	 * @return TokenStorageInterface
	 */
	public function clearAllTokens() {
	}

	/**
	 * Store the authorization state related to a given service
	 *
	 * @param string $service
	 * @param string $state
	 *
	 * @return TokenStorageInterface
	 */
	public function storeAuthorizationState($service, $state){
		return $this;
	}

	/**
	 * Check if an authorization state for a given service exists
	 *
	 * @param string $service
	 *
	 * @return bool
	 */
	public function hasAuthorizationState($service){
		
	}

	/**
	 * Retrieve the authorization state for a given service
	 *
	 * @param string $service
	 *
	 * @return string
	 */
	public function retrieveAuthorizationState($service){
		
	}

	/**
	 * Clear the authorization state of a given service
	 *
	 * @param string $service
	 *
	 * @return TokenStorageInterface
	 */
	public function clearAuthorizationState($service){
		
	}

	/**
	 * Delete *ALL* user authorization states. Use with care. Most of the time you will likely
	 * want to use clearAuthorization() instead.
	 *
	 * @return TokenStorageInterface
	 */
	public function clearAllAuthorizationStates(){
		
	}
}