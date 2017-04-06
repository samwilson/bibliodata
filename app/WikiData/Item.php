<?php

namespace App\WikiData;

use Exception;
use Illuminate\Support\Facades\Cache;
use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\VarDumper\VarDumper;

class Item {

    const PROP_INSTANCE_OF = 'P31';
    const PROP_TITLE = 'P1476';
    const PROP_IMAGE = 'P18';
    const PROP_AUTHOR = 'P50';
    const PROP_EDITION_OR_TRANSLATION_OF = 'P629';
    const PROP_PUBLICATION_DATE = 'P577';
    const PROP_PUBLISHER = 'P123';

    /** @var string */
    protected $id;

    /** @var MediawikiApi */
    protected $wdApi;

    /** @var string */
    protected $lang;
    
    protected function __construct($id, $lang)
    {
        if (!is_string($id) || preg_match('/[QP][0-9]*/i', $id) !== 1) {
            throw new Exception("Not a valid ID: ".var_export($id, true));
        }
        $this->id = $id;
        $this->wdApi = new MediawikiApi('https://www.wikidata.org/w/api.php');
        $this->entities = [];
        $this->lang = $lang;
    }

    /**
     * Create a new Item object with class based on the item's 'instance of' statement.
     * @param string $id
     * @param string $lang
     * @return Item
     */
    public static function factory($id, $lang)
    {
        $item = new Item($id, $lang);
        foreach ($item->getPropertyOfTypeItem($id, self::PROP_INSTANCE_OF) as $instanceOf) {
            // Try to find a class mating the 'instance of' name.
            $possibleClassName = __NAMESPACE__.'\\'.studly_case($instanceOf->getTitle()).'Item';
            if (class_exists($possibleClassName)) {
                // This won't re-request the metadata, because that's cached.
                return new $possibleClassName($id, $lang);
            }
        }
        // If we're here, just leave it as a basic Item.
        return $item;
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

    public function getStandardProperties($type = 'work')
    {
        if ($type !== 'work') {
            $type = 'edition';
        }
        $cacheKey = $type.'_item_property_IDs';
        if (Cache::has($cacheKey)) {
            $propIds = Cache::get($cacheKey);
        } else {
            $domCrawler = new Crawler();
            $domCrawler->addHtmlContent(file_get_contents('https://www.wikidata.org/wiki/Wikidata:WikiProject_Books'));
            $propCells = $domCrawler->filterXPath("//h3/span[@id='".ucfirst($type)."_item_properties']/../following-sibling::table[1]//td[2]/a");
            $propIds = [];
            $propCells->each(function(Crawler $node, $i) use (&$propIds) {
                $propId = $node->text();
                $propIds[] = $propId;
            });
            Cache::put($cacheKey, $propIds, 60);
        }
        $workProperties = [];
        foreach ($propIds as $propId) {
            $workProperties[] = new Item($propId, $this->lang);
        }
        return $workProperties;
    }

    protected function getPropertyOfTypeTime($entityId, $propertyId, $dateFormat = 'Y')
    {
        $entity = $this->getEntity($entityId);
        if (!isset($entity['claims'][$propertyId])) {
            return false;
        }
        foreach ($entity['claims'][$propertyId] as $claims) {
            foreach ($claims as $claim) {
                $timeValue = $claim['datavalue']['value']['time'];
                // Ugly workaround for imprecise dates. :-(
                if (preg_match('/([0-9]{1,4})-00-00/', $timeValue, $matches) === 1) {
                    $timeValue = $matches[1];
                    return $timeValue;
                }
                $time = strtotime($timeValue);
                return date($dateFormat, $time);
            }
        }
    }

    /**
     * @param $entityId
     * @param $propertyId
     * @return bool|Item[]
     */
    protected function getPropertyOfTypeItem($entityId, $propertyId)
    {
        $entity = $this->getEntity($entityId);
        if (!isset($entity['claims'][$propertyId])) {
            return [];
        }
        $items = [];
        foreach ($entity['claims'][$propertyId] as $claim) {
            $items[] = Item::factory($claim['mainsnak']['datavalue']['value']['id'], $this->lang);
        }
        return $items;
    }

    public function getPropertyOfTypeUrl($entityId, $propertyId)
    {
        $entity = $this->getEntity($entityId);
        if (!isset($entity['claims'][$propertyId])) {
            return false;
        }
        $urls = [];
        foreach ($entity['claims'][$propertyId] as $claim) {
            $urls[] = $claim['mainsnak']['datavalue']['value'];
        }
        return $urls;
    }

    public function getPropertyOfTypeExternalIdentifier($entityId, $propertyId)
    {
        $entity = $this->getEntity($entityId);
        if (!isset($entity['claims'][$propertyId])) {
            return false;
        }
        $idents = [];
        foreach ($entity['claims'][$propertyId] as $claim) {
            $qualifiers = [];
            foreach (array_get($claim, 'qualifiers', []) as $qualsInfo) {
                foreach ($qualsInfo as $qualInfo) {
                    $qualProp = new Item($qualInfo['property'], 'en');
                    $propTitle = $qualProp->getTitle();
                    if (!isset($qualifiers[$propTitle])) {
                        $qualifiers[$propTitle] = [];
                    }
                    $qualifiers[$propTitle][] = $qualInfo['datavalue']['value'];
                }
            }
            $idents[] = [
                'qualifiers' => $qualifiers,
                'value' => $claim['mainsnak']['datavalue']['value'],
            ];
        }
        return $idents;
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
            // Use the first title.
            foreach ($entity['claims'][self::PROP_TITLE] as $t) {
                if ($t['mainsnak']['datavalue']['value']['language'] == $this->lang) {
                    return $t['mainsnak']['datavalue']['value']['text'];
                }
            }
        } elseif (isset($entity['labels'][$this->lang]['value'])) {
            // Or use the label in this language.
            return $entity['labels'][$this->lang]['value'];
        } else {
            // Or just use the ID.
            return $entity['id'];
        }
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
        $cacheKey = 'wikipedia-intro-'.$this->id.$this->lang;
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
                $extract = $page['extract'];
                Cache::put($cacheKey, $extract, 24*60);
                return $extract;
            }
        }
        return '';
    }
    
    /**
     * Get URLs of images for this item.
     * @return string[]
     */
    public function getImages()
    {
        return [];
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