<?php

namespace App\Http\Controllers;

use App\WikiData\Item;
use App\WikiData\ItemList;
use App\WikiData\Query;
use Illuminate\Http\Request;

class ItemController extends Controller
{

    /** @var string The current language. */
    protected $lang = 'en';

    public function item(Request $request, $itemId)
    {
        $item = Item::factory($itemId, $this->lang);

        if (!$item->exists()) {
            abort(404, "Item $itemId not found");
        }

        if ($item->getWorkId()) {
            $request->session()->flash('alert-success', "You have been redirected (the requested item is an edition of this work).");
            return redirect(route('item', ['id' => $item->getWorkId()]));
        }

        return view('item')
            ->with('lang', $this->lang)
            ->with('work', $item)
            ->with('title', $item->getTitle());
    }

    public function search(Request $request)
    {
        $term = $request->get('term');

        // See if it's a Q-number.
        if (preg_match('/Q[0-9]+/i', $term) === 1) {
            return redirect(route('item', ['id' => $term]));
        }

        // Otherwise, run the query.
        $results = [];
        if ($term) {
            $itemList = new Query("SELECT ?item WHERE
                {
                  ?item wdt:P31* wd:Q571 .
                  ?item rdfs:label ?label .
                  FILTER(
                    CONTAINS(LCASE(?label), '".addslashes(strtolower($term))."')
                    && LANG(?label) = '$this->lang'
                  ) .
                } LIMIT 10", $this->lang);
            $results = $itemList->getItems();
        }
        return view('search_results')
            ->with('term', $term)
            ->with('lang', $this->lang)
            ->with('results', $results);
    }

}
