<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */

namespace qtism\runtime\rendering\markup\xhtml;

use qtism\runtime\rendering\AbstractRenderingContext;
use qtism\data\QtiComponent;
use qtism\runtime\rendering\AbstractRenderer;
use \DOMDocumentFragment;

/**
 * BodyElement renderer.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class BodyElementRenderer extends AbstractXhtmlRenderer {
    
    /**
     * A set of additional CSS classes to be added
     * to the rendered element.
     *
     * @var array
     */
    private $additionalClasses = array();
    
    /**
     * Create a new BodyElementRenderer object.
     *
     * @param AbstractRenderingContext $renderingContext An optional rendering context to use e.g. when outside of a rendering engine.
     */
    public function __construct(AbstractRenderingContext $renderingContext = null) {
        parent::__construct($renderingContext);
    }
    
    public function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component) {
        
        if ($component->hasId() === true) {
            $fragment->firstChild->setAttribute('id', $component->getId());
        }
        
        if ($component->hasClass() === true) {
            $fragment->firstChild->setAttribute('class', $component->getClass());
        }
        
        if ($component->hasLang() === true) {
            $fragment->firstChild->setAttribute('lang', $component->getLang());
        }
        
        if ($this->hasAdditionalClasses() === true) {
            $classes = implode("\x20", $this->getAdditionalClasses());
            $currentClasses = $fragment->firstChild->getAttribute('class');
            $glue = ($currentClasses !== '') ? "\x20" : "";
            $fragment->firstChild->setAttribute('class', $currentClasses . $glue . $classes);
        }
    }
    
    /**
     * Set the array of additional CSS classes.
     *
     * @param array $additionalClasses
     */
    protected function setAdditionalClasses(array $additionalClasses) {
        $this->additionalClasses = $additionalClasses;
    }
    
    /**
     * Get the array of additional CSS classes.
     *
     * @return array
     */
    protected function getAdditionalClasses() {
        return $this->additionalClasses;
    }
    
    /**
     * Whether additional CSS classes are defined for rendering.
     *
     * @return boolean
     */
    protected function hasAdditionalClasses() {
        return count($this->getAdditionalClasses()) > 0;
    }
    
    /**
     * Add an additional CSS class to be rendered.
     *
     * @param string $additionalClass A CSS class.
     */
    public function additionalClass($additionalClass) {
        $additionalClasses = $this->getAdditionalClasses();
        $additionalClasses[] = $additionalClass;
        $this->setAdditionalClasses(array_unique($additionalClasses));
    }
}