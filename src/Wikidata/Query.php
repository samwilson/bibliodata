<?php

namespace Samwilson\Bibliodata\Wikidata;

use Exception;
use Psr\Cache\CacheItemPoolInterface;
use SimpleXmlElement;

class Query {

	/** @var string */
	protected $query;
	/** @var string */
	protected $lang;
	/** @var CacheItemPoolInterface */
	protected $cache;

	public function __construct( $query, $lang = 'en' ) {
		$this->query = $query;
		$this->lang  = $lang;
	}

	public function setCache(CacheItemPoolInterface $cache_item_pool) {
		$this->cache = $cache_item_pool;
	}

	/**
	 * Get the items.
	 * @return Item[] The results.
	 */
	public function getItems() {
		$xml     = $this->getXml( $this->query );
		$results = [];
		foreach ( $xml->results->result as $res ) {
			$result    = $this->getBindings( $res );
			$id        = substr( $result['item'], strrpos( $result['item'], '/' ) + 1 );
			$item      = Item::factory( $id, $this->lang, $this->cache );
			$results[] = $item;
		}

		return $results;
	}

	private function getXml( $query ) {
		$url = "https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=" . urlencode( $query );
		try {
			$result = file_get_contents( $url );
		} catch ( Exception $e ) {
			throw new Exception( "Unable to run query: <pre>" . htmlspecialchars( $query ) . "</pre>", 500 );
		}
		if ( empty( $result ) ) {
			// @OTODO: sort out a proper error handler! :(
			header( 'Content-type:text/plain' );
			echo 'ERROR running query: '.$query;
			exit( 1 );
		}
		$xml = new SimpleXmlElement( $result );

		return $xml;
	}

	private function getBindings( $xml ) {
		$out = [];
		foreach ( $xml->binding as $binding ) {
			if ( isset( $binding->literal ) ) {
				$out[ (string) $binding['name'] ] = (string) $binding->literal;
			}
			if ( isset( $binding->uri ) ) {
				$out[ (string) $binding['name'] ] = (string) $binding->uri;
			}
		}

		// print_r($out);
		return $out;
	}

}
