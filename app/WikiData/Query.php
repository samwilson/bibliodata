<?php

namespace App\WikiData;

use Exception;
use SimpleXmlElement;
use Symfony\Component\VarDumper\VarDumper;

class Query
{

    protected $query;
    protected $lang;

    public function __construct($query, $lang = 'en')
    {
        $this->query = $query;
        $this->lang = $lang;
    }

    /**
     * Get the items.
     * @return Item[] The results.
     */
    public function getItems()
    {
        $xml = $this->getXml($this->query);
        $results = [];
        foreach ($xml->results->result as $res) {
            $result = $this->getBindings($res);
            $id = substr($result['item'], strrpos($result['item'], '/') + 1);
            $item = Item::factory($id, $this->lang);
            $results[] = $item;
        }
        return $results;
    }
    
    private function getXml($query)
    {
        $url = "https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=" . urlencode($query);
        try {
            $result = file_get_contents($url);
        } catch (Exception $e) {
            throw new Exception("Unable to run query: <pre>" . htmlspecialchars($query) . "</pre>", 500);
        }
        if (empty($result)) {
            // @OTODO: sort out a proper error handler! :(
            header('Content-type:text/plain');
            echo $query;
            exit(1);
        }
        $xml = new SimpleXmlElement($result);
        return $xml;
    }

    private function getBindings($xml)
    {
        $out = [];
        foreach ($xml->binding as $binding) {
            if (isset($binding->literal)) {
                $out[(string)$binding['name']] = (string)$binding->literal;
            }
            if (isset($binding->uri)) {
                $out[(string)$binding['name']] = (string)$binding->uri;
            }
        }
        // print_r($out);
        return $out;
    }
    
}
