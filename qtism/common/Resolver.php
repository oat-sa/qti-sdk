<?php

namespace qtism\common;

interface Resolver {
	
	/**
	 * Resolve a given URL (Uniform Resource Locator).
	 * 
	 * @param string $url A URL to resolve.
	 * @return string A resolved URL.
	 * @throws ResolutionException If an error occurs during the resolution of $url.
	 */
	public function resolve($url);
	
}