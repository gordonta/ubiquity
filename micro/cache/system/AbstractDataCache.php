<?php
/**
 * Inspired by (c) Rasmus Schultz <rasmus@mindplay.dk>
 * <https://github.com/mindplay-dk/php-annotations>
 */
namespace micro\cache\system;

/**
 * This class is responsible for storing Arrays in PHP files.
 */
abstract class AbstractDataCache {
	/**
	 *
	 * @var string The PHP opening tag (used when writing cache files)
	 */
	const PHP_TAG="<?php\n";


	protected $_root;

	protected $postfix;

	public function __construct($root, $postfix=""){
		$this->_root=$root;
		$this->postfix=$postfix;
	}
	/**
	 * Check if annotation-data for the key has been stored.
	 * @param string $key cache key
	 * @return boolean true if data with the given key has been stored; otherwise false
	 */
	abstract public function exists($key);

	public function expired($key, $duration) {
		if ($this->exists($key)) {
			if (\is_int($duration)) {
				return \time() - $this->getTimestamp($key) > $duration;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	/**
	 * Caches the given data with the given key.
	 * @param string $key cache key
	 * @param string $code the source-code to be cached
	 * @throws AnnotationException if file could not be written
	 */
	public function store($key, $code, $php=true) {
		$content="";
		if ($php)
			$content=self::PHP_TAG;
		$content.=$code . "\n";
		$this->storeContent($key, $content);
	}

	public function getRoot() {
		return $this->_root;
	}

	abstract protected function storeContent($key,$content);

	/**
	 * Fetches data stored for the given key.
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	abstract public function fetch($key);

	/**
	 * return data stored for the given key.
	 * @param string $key cache key
	 * @return mixed the cached data
	 */
	abstract public function file_get_contents($key);

	/**
	 * Returns the timestamp of the last cache update for the given key.
	 *
	 * @param string $key cache key
	 * @return int unix timestamp
	 */
	abstract public function getTimestamp($key);

	abstract public function remove($key);

	abstract public function clear();

}