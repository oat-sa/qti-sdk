<?php

namespace qtism\data;

use \InvalidArgumentException;

interface Document {
	
	/**
	 * Set the QTI version of the document.
	 *
	 * @param string $version A QTI version.
	 */
	public function setVersion($version);
	
	/**
	 * Get the QTI version of the document.
	 *
	 * @return string A QTI version.
	*/
	public function getVersion();
	
	/**
	 * Save the Document at a specific location.
	 * 
	 * @param string $url The URI (Uniform Resource Identifier) describing the location where to save the file.
	 */
	public function save($uri);
	
	/**
	 * Save the Document from a specific location.
	 * 
	 * @param string $url The URI (Uniform Resource Identifier) describing the location from where the file has to be loaded.
	 */
	public function load($uri);
	
	/**
	 * Get the URI describing how/where the loaded/saved document is located. If the implementation
	 * is not aware yet of this location, an empty string ('') is returned.
	 * 
	 * @return string A Uniform Resource Identifier (URI) or an empty string.
	 */
	public function getUri();
}