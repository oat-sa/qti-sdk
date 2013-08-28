<?php
namespace qtism\data\storage;

use qtism\common\ResolutionException;

/**
 * The LocalFileResolver class resolve relative paths to canonical
 * ones using a given base path.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class LocalFileResolver extends FileResolver {
	
	/**
	 * Create a new LocalFileResolver object.
	 * 
	 * @param string $basePath The base path from were the URLs will be resolved.
	 * @throws InvalidArgumentException If $basePath is not a valid string value.
	 */
	public function __construct($basePath = '') {
		parent::__construct($basePath);
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