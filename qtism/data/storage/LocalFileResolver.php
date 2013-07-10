<?php
namespace qtism\data\storage;

use qtism\common\Resolver;
use qtism\common\ResolutionException;
use \InvalidArgumentException;

/**
 * The LocalFileResolver class resolve relative paths to canonical
 * ones using a given base path.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class LocalFileResolver implements Resolver {
	
	private $basePath = '';
	
	/**
	 * Create a new LocalFileResolver object.
	 * 
	 * @param string $basePath The base path from were the URLs will be resolved.
	 * @throws InvalidArgumentException If $basePath is not a valid string value.
	 */
	public function __construct($basePath) {
		$this->setBasePath($basePath);
	}
	
	/**
	 * Set the base path from where the URLs will be resolved.
	 * 
	 * @param string $basePath A base path.
	 * @throws InvalidArgumentException If $basePath is not a valid string value.
	 */
	public function setBasePath($basePath) {
		if (is_string($basePath)) {
			$this->basePath = $basePath;
		}
		else {
			$msg = "The basePath argument must be a valid string, '" . gettype($basePath) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
	
	/**
	 * Get the base path from where the URLs will be resolved.
	 * 
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}
	
	/**
	 * Resolve a relative $url to a canonical path based
	 * on the LocalFileResolver's base path.
	 * 
	 * @param string $url A URL to be resolved.
	 * @throws ResolutionException If $url cannot be resolved.
	 */
	public function resolve($url) {
		$baseUrl = Utils::sanitizeUri($this->getBasePath());
		$baseDir = pathinfo($baseUrl, PATHINFO_DIRNAME);
			
		if (empty($baseDir)) {
			$msg = "The base directory of the document ('${baseDir}') could not be resolved.";
			throw new ResolutionException($msg);
		}
			
		$href = $baseDir . '/' . ltrim($url, '/');
		return $href;
	}
}