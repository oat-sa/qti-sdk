<?php

namespace qtism\data\storage;

use qtism\common\Resolver;
use \InvalidArgumentException;

/**
 * The base class of FileResolvers.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class FileResolver implements Resolver {
    
    /**
     * A base path.
     * 
     * @var string
     */
    private $basePath = '';
    
    /**
     * Create a new FileResolver object.
     * 
     * @param string $basePath A base path.
     * @throws InvalidArgumentException If $basePath is not a string value.
     */
    public function __construct($basePath = '') {
        $this->setBasePath($basePath);
    }
    
    /**
     * Set the basePath.
     * 
     * @param string $basePath A base path.
     * @throws InvalidArgumentException If $basePath is not a string value.
     */
    public function setBasePath($basePath = '') {
		if (is_string($basePath)) {
			$this->basePath = $basePath;
		}
		else {
			$msg = "The basePath argument must be a valid string, '" . gettype($basePath) . "' given.";
			throw new InvalidArgumentException($msg);
		}
	}
    
	/**
	 * Get the base path.
	 * 
	 * @return string A base path.
	 */
    public function getBasePath() {
		return $this->basePath;
	}
}