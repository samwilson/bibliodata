<?php

namespace App\Http\Controllers;

use MediaWiki\OAuthClient\Client;
use MediaWiki\OAuthClient\ClientConfig;

class UserController
{
    
    public function login()
    {
        $client = new Client(new ClientConfig("http://www.wikidata.org/"));
        $client->setCallback( $c->settings['oauth.callback'] );
        
        list( $next, $token ) = $client->initiate();
        
        $_SESSION[self::REQEST_KEY] = "{$token->key}:{$token->secret}";
        $this->redirect( $next );
    }
}