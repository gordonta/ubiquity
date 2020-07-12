<?php

namespace Ubiquity\cache\system;

use Ubiquity\cache\CacheFile;

/**
 * This class is responsible for storing values with Redis.
 * Ubiquity\cache\system$RedisCacheDriver
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class RedisCacheDriver extends AbstractDataCache {
	/**
	 *
	 * @var \Redis
	 */
	private $cacheInstance;

	/**
	 * Initializes the cache-provider
	 */
	public function __construct($root, $postfix = "", $cacheParams = [ ]) {
		parent::__construct ( $root, $postfix );
		$defaultParams = [ 'server' => '0.0.0.0','port' => 6379,'persistent' => true ];
		$cacheParams = \array_merge ( $defaultParams, $cacheParams );
		$this->cacheInstance = new \Redis ();
		$connect = 'connect';
		if ($cacheParams ['persistent'] ?? true) {
			$connect = 'pconnect';
		}
		$this->cacheInstance->{$connect} ( $cacheParams ['server'], $cacheParams ['port'] );
	}

	/**
	 * Check if annotation-data for the key has been stored.
	 *
	 * @param string $key cache key
	 * @return boolean true if data with the given key has been stored; otherwise false
	 */
	public function exists($key) {
		$k = $this->getRealKey ( $key );
		return $this->cacheInstance->exists ( $k );
	}

	public function store($key, $code, $tag = null, $php = true) {
		$this->storeContent ( $key, $code, $tag );
	}

	/**
	 * Caches the given data with the given key.
	 *
	 * @param string $key cache key
	 * @param string $content the source-code to be cached
	 * @param string $tag
	 */
	protected function storeContent($key, $content, $tag) {
		$key = $this->getRealKey ( $key );
		$this->cacheInstance->set ( $key, $content );
	}

	protected function getRealKey($key) {
		return \str_replace ( [ '/','\\' ], "-", $key );
	}

	/**
	 * Fetches data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function fetch($key) {
		return $this->cacheInstance->get ( $this->getRealKey ( $key ) );
	}

	/**
	 * return data stored for the given key.
	 *
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	public function file_get_contents($key) {
		return $this->cacheInstance->get ( $this->getRealKey ( $key ) );
	}

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key cache key
	 * @return int unix timestamp
	 */
	public function getTimestamp($key) {
		$key = $this->getRealKey ( $key );
		return $this->cacheInstance->ttl ( $key );
	}

	public function remove($key) {
		$key = $this->getRealKey ( $key );
		$this->cacheInstance->delete ( $this->getRealKey ( $key ) );
	}

	public function clear() {
		$this->cacheInstance->flushAll ();
	}

	public function getCacheFiles($type) {
		$result = [ ];
		$keys = $this->cacheInstance->keys ( $type );

		foreach ( $keys as $key ) {
			$ttl = $this->cacheInstance->ttl ( $key );
			$result [] = new CacheFile ( \ucfirst ( $type ), $key, $ttl, "", $key );
		}
		if (\sizeof ( $result ) === 0)
			$result [] = new CacheFile ( \ucfirst ( $type ), "", "", "" );
		return $result;
	}

	public function clearCache($type) {
		$keys = $this->cacheInstance->keys ( $type );
		foreach ( $keys as $key ) {
			$this->cacheInstance->delete ( $key );
		}
	}

	public function getCacheInfo() {
		return parent::getCacheInfo () . "<br>Driver name : <b>" . \Redis::class . "</b>";
	}

	public function getEntryKey($key) {
		return $this->getRealKey ( $key );
	}
}
