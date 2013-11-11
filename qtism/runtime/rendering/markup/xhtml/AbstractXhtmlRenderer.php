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

use qtism\data\QtiComponent;
use qtism\runtime\rendering\AbstractRenderer;
use \DOMDocumentFragment;

/**
 * Base class of all XHTML renderers.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractXhtmlRenderer extends AbstractRenderer {
    
    /**
     * Create a new XhtmlAbstractRenderer object.
     *
     */
    public function __construct() {
        
    }
    
    /**
     * Render a QtiComponent into a DOMDocumentFragment that will be registered
     * in the current rendering context.
     */
    public function render(QtiComponent $component) {
        $doc = $this->getRenderingContext()->getDocument();
        $fragment = $doc->createDocumentFragment();
        
        $this->appendElement($fragment, $component);
        $this->appendChildren($fragment, $component);
        $this->appendAttributes($fragment, $component);
        
        $this->getRenderingContext()->storeRendering($fragment);
        
        return $fragment;
    }
    
    /**
     * Append a new DOMElement to the currently rendered $fragment which is suitable
     * to $component.
     * 
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     */
    abstract protected function appendElement(DOMDocumentFragment $fragment, QtiComponent $component);
    
    /**
     * Append the children renderings of $components to the currently rendered $fragment.
     * 
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     */
    protected function appendChildren(DOMDocumentFragment $fragment, QtiComponent $component) {
        $element = $fragment->firstChild;
        foreach ($this->getRenderingContext()->getChildrenRenderings($component) as $childrenRendering) {
            $element->appendChild($childrenRendering->firstChild);
        }
    }
    
    /**
     * Append the necessary attributes of $component to the currently rendered $fragment.
     * 
     * @param DOMDocumentFragment $fragment
     * @param QtiComponent $component
     */
    abstract protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component);
}