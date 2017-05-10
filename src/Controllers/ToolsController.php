<?php

namespace Samwilson\Bibliodata\Controllers;

use Samwilson\Bibliodata\Wikidata\Item;
use Samwilson\Bibliodata\Wikidata\Query;

class ToolsController extends ControllerBase {

	public function index() {

		$term = isset($_REQUEST['term']) ? $_REQUEST['term'] : '';

		// See if it's a Q-number.
		if (preg_match('/Q[0-9]+/i', $term) === 1) {
			wp_redirect('tools.php?page=bibliodata-tools&item='.$term);
			exit;
		}

		// Otherwise, run the query.
		$results = [];
		if ($term) {
			$bookItem = 'Q571';
			$itemList = new Query("SELECT ?item WHERE
				{
					?item wdt:".Item::PROP_INSTANCE_OF." wd:$bookItem .
					?item rdfs:label ?label .
					FILTER(
						CONTAINS(LCASE(?label), '".addslashes(strtolower($term))."')
						&& LANG(?label) = 'en'
					) .
				} LIMIT 10");
			$itemList->setCache($this->cache);
			$results = $itemList->getItems();
		}
		require_once WP_PLUGIN_DIR.'/bibliodata/templates/search.html.php';
	}

	public function edit() {
		if (!isset($_REQUEST['item']) || preg_match('/Q[0-9]+/i', $_REQUEST['item']) !== 1) {
			wp_redirect('tools.php?page=bibliodata-tools');
			exit;
		}
		$work = Item::factory($_REQUEST['item'], 'en', $this->cache);
		require_once WP_PLUGIN_DIR.'/bibliodata/templates/edit.html.php';
	}

	public function create() {
		Item::create('en', $this->cache);
	}

	public function save() {
		$work = Item::factory($_REQUEST['item'], 'en', $this->cache);
		var_dump($_REQUEST['']);

		wp_redirect('tools.php?page=bibliodata-tools&action=edit&item='.$work->getId());
		exit;
	}
}
