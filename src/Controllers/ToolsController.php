<?php

namespace Samwilson\Bibliodata\Controllers;

use Samwilson\Bibliodata\Wikidata\BookItem;
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
			$itemList = new Query("SELECT ?item WHERE
				{
					?item wdt:" . Item::PROP_INSTANCE_OF . " wd:" . BookItem::ITEM_BOOK . " .
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
		//if (!isset($_REQUEST['item']) || preg_match('/Q[0-9]+/i', $_REQUEST['item']) !== 1) {
			//wp_redirect('tools.php?page=bibliodata-tools');
			//echo 'Please specify an item ID.';
			//return;
		//}
		$work = false;
		if (isset($_REQUEST['item'])) {
			$itemId = isset($_REQUEST['item']) ? $_REQUEST['item'] : null;
			$work = Item::factory($itemId, 'en', $this->cache);
		}
		require_once WP_PLUGIN_DIR.'/bibliodata/templates/edit.html.php';
	}

	public function save() {
		if (!isset($_POST['save'])) {
			return;
		}
		// Create or load the item.
		if (isset($_POST['item'])) {
			$work = Item::factory($_REQUEST['item'], 'en', $this->cache);
		} else {
			//$work = Item::create( 'en', $this->cache);
		}

		// Make sure it's a book.
		$work->setPropertyOfTypeItem( Item::PROP_INSTANCE_OF, BookItem::ITEM_BOOK );

		// Set title and subtitle.
		if (isset($_POST['title'])) {
			$work->setTitle( $_POST['title']);
		}
		if (isset($_POST['subtitle'])) {
			$work->setSubtitle( $_POST['subtitle']);
		}

		// Redirect back to the same edit form.
		wp_redirect('tools.php?page=bibliodata-tools&action=edit&item='.$work->getId());
		exit;
	}
}
