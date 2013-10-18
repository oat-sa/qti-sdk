<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *   
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * 
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package 
 */

namespace qtism\data\storage\php;

use qtism\data\AssessmentTest;
use qtism\data\Document;

/**
 * A class representing QTI AssessmentTest stored as PHP source code.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class PhpAssessmentTestDocument extends AssessmentTest implements Document {
    
    /**
     * The QTI version of the PhpAssessmentTestDocument.
     * 
     * @var string
     */
    private $version = '2.1';
    
    /**
     * The PhpDocument to be used to load/save the data.
     * 
     * @var PhpDocument
     */
    private $phpDocument;
    
    /**
     * The URI of the document.
     * 
     * @var string
     */
    private $uri = '';
    
    /**
     * Create a new PhpAssessmentTestDocument object.
     * 
     */
    public function __construct($version = '2.1') {
        parent::__construct('noIdentifier', 'noTitle');
        $this->setVersion($version);
        $this->setPhpDocument(new PhpDocument($this));
    }
    
    /**
     * Set the QTI version of the document.
     * 
     * @param string $version A version number e.g. '2.1'.
     */
    public function setVersion($version) {
        $this->version = $version;
    }
    
    /**
     * Get the QTI version of the document.
     * 
     * @return string A version number e.g. '2.1'.
     */
    public function getVersion() {
        return $this->version;
    }
    
    /**
     * Set the PhpDocument object that is used to load/save data.
     * 
     * @param PhpDocument $phpDocument A PhpDocument object.
     */
    protected function setPhpDocument(PhpDocument $phpDocument) {
        $this->phpDocument = $phpDocument;
    }
    
    /**
     * Get the PhpDocument object that is used to load/save data.
     * 
     * @return PhpDocument A PhpDocument object.
     */
    public function getPhpDocument() {
        return $this->phpDocument;
    }
    
    protected function setUri($uri) {
        $this->uri = $uri;
    }
    
    public function getUri() {
        return $this->uri;
    }
    
    /**
     * Load the PHP QTI document located at $url.
     * 
     * @param string $url The URL (Uniform Resource Locator) describing the location of the document to be loaded.
     * @throws PhpStorageException If an error occurs while loading the PHP QTI document.
     */
    public function load($url) {
        $this->getPhpDocument()->load($url);
        $documentComponent = $this->getPhpDocument()->getDocumentComponent();
        $this->setIdentifier($documentComponent->getIdentifier());
        $this->setTitle($documentComponent->getTitle());
        $this->setToolName($documentComponent->getToolName());
        $this->setToolVersion($documentComponent->getToolVersion());
        $this->setOutcomeDeclarations($documentComponent->getOutcomeDeclarations());
        $this->setTimeLimits($documentComponent->getTimeLimits());
        $this->setTestParts($documentComponent->getTestParts());
        $this->setOutcomeProcessing($documentComponent->getOutcomeProcessing());
        $this->setTestFeedbacks($documentComponent->getTestFeedbacks());
        
        $this->setUri($url);
    }
    
    /**
     * Save the PHP QTI document located at $url.
     * 
     * @param string $url The URL (Uniform Resource Locator) describing where the document must be saved.
     * @throws PhpStorageException If an error occurs while saving the document.
     */
    public function save($url) {
        $this->getPhpDocument()->save($url);
        $this->setUri($url);
    }
}