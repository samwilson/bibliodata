<?php

namespace Samwilson\Bibliodata\Wikidata;

class BookItem extends Item {

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

	public function getEditions() {
		$sparql   = "SELECT ?item WHERE { ?item wdt:" . self::PROP_EDITION_OR_TRANSLATION_OF . " wd:" . $this->getId() . " }";
		$query    = new Query( $sparql, $this->lang );
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