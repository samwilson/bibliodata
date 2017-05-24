<?php

namespace Samwilson\Bibliodata\Wikidata;

class BookItem extends Item {

	const ITEM_BOOK = 'Q571';
	const PROP_SUBTITLE = 'P1680';
	const PROP_GENRE = 'P136';
	const PROP_SUBJECT = 'P921';

	public static function getBookTypes($lang = 'en', $cache) {
		$sparql = "SELECT ?item WHERE {
			?item wdt:P279 wd:Q571 .
			?item rdfs:label ?label .
			FILTER(LANG(?label) = '$lang') .
			} ORDER BY ?label ";
		$query = new Query($sparql, $lang);
		$query->setCache($cache);
		$bookType = Item::factory(self::ITEM_BOOK, $lang, $cache);
		return [$bookType] + $query->getItems();
	}

	public function getSubtitle() {
		return $this->getPropertyOfTypeText(self::PROP_SUBTITLE);
	}

	public function setSubtitle( $subtitle ) {
		$this->setPropertyOfTypeText( self::PROP_SUBTITLE, $subtitle );
	}

	public function getAuthors() {
		$entity = $this->getEntity( $this->id );
		if ( ! isset( $entity['claims'][ self::PROP_AUTHOR ] ) ) {
			return [];
		}
		$authors = [];
		foreach ( $entity['claims'][ self::PROP_AUTHOR ] as $authorClaim ) {
			$authorId  = $authorClaim['mainsnak']['datavalue']['value']['id'];
			$author    = Item::factory( $authorId, $this->lang, $this->cache );
			$authors[] = $author;
		}

		return $authors;
	}

	/**
	 * @return Item[]
	 */
	public function getSubjects() {
		return [];
	}

	public function getEditions() {
		$sparql = "SELECT ?item WHERE { ?item wdt:" . self::PROP_EDITION_OR_TRANSLATION_OF . " wd:" . $this->getId() . " }";
		$query = new Query( $sparql, $this->lang );
		$query->setCache($this->cache);
		$editions = $query->getItems();
		usort( $editions, function ( Item $a, Item $b ) {
			if ( $a instanceof EditionItem and $b instanceof EditionItem ) {
				return $a->getPublicationYear() - $b->getPublicationYear();
			}
			return 0;
		} );

		return $editions;
	}

	public function newEdition() {
		
	}

}