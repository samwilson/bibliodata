<?php

namespace App\WikiData;

use Illuminate\Support\Facades\Cache;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\VarDumper\VarDumper;

class Item {

    const PROP_TITLE = 'P1476';
    const PROP_IMAGE = 'P18';
    const PROP_AUTHOR = 'P50';
    const PROP_EDITION_OR_TRANSLATION_OF = 'P629';
    const PROP_PUBLICATION_DATE = 'P577';

    /** @var string */
    protected $id;

    /** @var MediawikiApi */
    protected $wdApi;

    /** @var string[] */
    protected $entities;
    
    /** @var string */
    protected $lang;
    
    public function __construct($id, $lang)
    {
        $this->id = $id;
        $this->wdApi = new MediawikiApi('https://www.wikidata.org/w/api.php');
        $this->entities = [];
        $this->lang = $lang;
    }

    public function getId()
    {
        $entity = $this->getEntity($this->id);
        return isset($entity['id']) ? $entity['id'] : false;
    }

    public function getWikidataUrl()
    {
        return "https://www.wikidata.org/wiki/$this->id";
    }

    public function getStandardProperties()
    {
        $cacheKey = 'work_item_properties';
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $domCrawler = new Crawler();
        $domCrawler->addHtmlContent(file_get_contents('https://www.wikidata.org/wiki/Wikidata:WikiProject_Books'));
        $propCells = $domCrawler->filterXPath("//h3/span[@id='Work_item_properties']/../following-sibling::table[1]//td[2]/a");
        $workProperties = [];
        $propCells->each(function(Crawler $node, $i) use (&$workProperties) {
            $propId = $node->text();
            $metadataRequest = new SimpleRequest('wbgetentities', ['ids' => $propId]);
            $metadata = $this->wdApi->getRequest($metadataRequest);
            if (!isset($metadata['success'])) {
                return;
            }
            $workProperties[] = $metadata['entities'][$propId];
        });
        Cache::put($cacheKey, $workProperties, 60);
        return $workProperties;
    }

    protected function getDateProperty($entityId, $propertyId, $dateFormat = 'Y')
    {
        $entity = $this->getEntity($entityId);
        if (!isset($entity['claims'][$propertyId])) {
            return false;
        }
        foreach ($entity['claims'][$propertyId] as $claims) {
            foreach ($claims as $claim) {
                $timeValue = $claim['datavalue']['value']['time'];
                $time = strtotime($timeValue);
                return date($dateFormat, $time);
            }
        }
    }

    public function getPublicationYear()
    {
        return $this->getDateProperty($this->id, self::PROP_PUBLICATION_DATE, 'Y');
    }

    /**
     * If this is an edition (i.e. is an edition or translation of another item), then this gets the work's ID.
     * Otherwise, it's false.
     * @return string|boolean 
     */
    public function getWorkId()
    {
        $entity = $this->getEntity($this->id);
        if (isset($entity['claims'][self::PROP_EDITION_OR_TRANSLATION_OF])) {
            return $entity['claims'][self::PROP_EDITION_OR_TRANSLATION_OF][0]['mainsnak']['datavalue']['value']['id'];
        }
        return false;
    }
    
    public function getTitle()
    {
        $entity = $this->getEntity($this->id);
        if (isset($entity['claims'][self::PROP_TITLE])) {
            foreach ($entity['claims'][self::PROP_TITLE] as $t) {
                if ($t['mainsnak']['datavalue']['value']['language'] == $this->lang) {
                    return $t['mainsnak']['datavalue']['value']['text'];
                }
            }
        } else {
            return $entity['labels'][$this->lang]['value'];
        }
    }

    public function getAuthors()
    {
        $entity = $this->getEntity($this->id);
        if (!isset($entity['claims'][self::PROP_AUTHOR])) {
            return [];
        }
        $authors = [];
        foreach ($entity['claims'][self::PROP_AUTHOR] as $authorClaim) {
            $authorId = $authorClaim['mainsnak']['datavalue']['value']['id'];
            $authors[] = new Item($authorId, $this->lang);
        }
        return $authors;
    }

    public function getEditions()
    {
        $sparql = "SELECT ?item WHERE { ?item wdt:".self::PROP_EDITION_OR_TRANSLATION_OF." wd:".$this->getId()." }";
        $itemList = new ItemList($sparql, $this->lang);
        $editions = $itemList->getItems();
        usort($editions, function(Item $a, Item $b){
            return $a->getPublicationYear() - $b->getPublicationYear();
        });
        return $editions;
    }

    /**
     * Does this item exist?
     * @return bool
     */
    public function exists()
    {
        return $this->getId() !== false;
    }

    
    public function getWikipediaIntro()
    {
        $cacheKey = 'wikipedia-intro-'.$this->lang;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $entity = $this->getEntity($this->id);
        if (!isset($entity['sitelinks'])) {
            return '';
        }
        foreach ($entity['sitelinks'] as $sitelink) {
            if ($sitelink['site'] == $this->lang.'wiki') {
                $api = new MediawikiApi('https://'.$this->lang.'.wikipedia.org/w/api.php');
                $req = new SimpleRequest('query', [
                    'prop' => 'extracts',
                    'exintro' => true,
                    'titles' => $sitelink['title'],
                ]);
                $response = $api->getRequest($req);
                $page = array_shift($response['query']['pages']);
                $extract = $page['']
                Cache::put($cacheKey, $extract);
                return $page['extract'];
            }
        }
        return '';
    }

    /**
     * Get the raw entity data from the 'wbgetentities' API call.
     * @param $id
     * @return bool
     */
    protected function getEntity($id)
    {
        $cacheKey = "entities.$id";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        $metadataRequest = new SimpleRequest('wbgetentities', ['ids' => $id]);
        $itemResult = $this->wdApi->getRequest($metadataRequest);
        if (!isset($itemResult['success']) || !isset($itemResult['entities'][$id])) {
            return false;
        }
        $metadata = $itemResult['entities'][$id];
        Cache::put($cacheKey, $metadata, 10);
        return $metadata;
    }

}