<?php

namespace Samwilson\Bibliodata\Controllers;

use Psr\Cache\CacheItemPoolInterface;

abstract class ControllerBase {

	/** @var CacheItemPoolInterface */
	protected $cache;

	/**
	 * @param CacheItemPoolInterface $cache_item_pool
	 */
	public function setCache(CacheItemPoolInterface $cache_item_pool) {
		$this->cache = $cache_item_pool;
	}
}
