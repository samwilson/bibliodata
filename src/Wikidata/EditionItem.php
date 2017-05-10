<?php

namespace Samwilson\Bibliodata\Wikidata;

class EditionItem extends Item {

    const PROP_WIKISOURCE_INDEX_PAGE = 'P1957';
    const PROP_SCANNED_FILE_ON_COMMONS = 'P996';
    const PROP_INTERNET_ARCHIVE_ID = 'P724';

    public function getPublicationYear()
    {
        return $this->getPropertyOfTypeTime($this->id, self::PROP_PUBLICATION_DATE, 'Y');
    }

    public function getPublishers()
    {
        return $this->getPropertyOfTypeItem($this->getId(), self::PROP_PUBLISHER);
    }

    public function getWikisourceIndexPages()
    {
        return $this->getPropertyOfTypeUrl($this->getId(), self::PROP_WIKISOURCE_INDEX_PAGE);
    }

    public function internetArchiveIds()
    {
        return $this->getPropertyOfTypeExternalIdentifier($this->getId(), self::PROP_INTERNET_ARCHIVE_ID);
    }

    /**
     * Get information about the Wikisource sitelink.
     * An edition should only ever be present on one Wikisource.
     * @return string[]
     */
    public function getWikisourceLink()
    {
        $entity = $this->getEntity($this->id);
        if (!isset($entity['sitelinks'])) {
            return [];
        }
        foreach ($entity['sitelinks'] as $sitelink) {
            if (strpos($sitelink['site'], 'wikisource') !== false) {
                $lang = substr($sitelink['site'], 0, strpos($sitelink['site'], 'wikisource'));
                return [
                    'title' => $sitelink['title'],
                    'url' => "https://$lang.wikisource.org/wiki/".$sitelink['title'],
                    'lang' => $lang,
                ];
            }
        }
        return [];
    }

}