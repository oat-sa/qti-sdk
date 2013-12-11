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
use \DOMDocumentFragment;

/**
 * HotspotChoice renderer, the base class of all renderers that render subclasses of
 * qti:hotspotChoice. This renderer will transform the choice into a 'div' element.
 * 
 * Depending on the value of the qti:choice->showHide attribute and only if 
 * a value for qti:choice->templateIdentifier is defined, an additional CSS class with
 * a value of 'qti-show' or 'qti-hide' will be set.
 * 
 * Moreover, the following data will be set to the data set of the element
 * with the help of the data-X attributes:
 * 
 * * data-identifier = qti:choice->identifier
 * * data-fixed = qti:choice->fixed
 * * data-templateIdentifier = qti:choice->templateIdentifier (only if qti:choice->templateIdentifier is set).
 * * data-showHide = qti:choice->showHide (only if qti:choice->templateIdentifier is set).
 * * data-shape = qti:hotspot->shape
 * * data-coords = qti:hotspot->coords
 * * data-hotspotLabel = qti:hotspot->hotspotLabel (only if qti:hotspotLabel is set).
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class HotspotChoiceRenderer extends HotspotRenderer {
    
    /**
     * Create a new HotspotChoiceRenderer.
     * 
     * @param AbstractRenderingContext $renderingContext
     */
    public function __construct(AbstractRenderingContext $renderingContext = null) {
        parent::__construct($renderingContext);
        $this->transform('div');
    }
    
    protected function appendAttributes(DOMDocumentFragment $fragment, QtiComponent $component) {
        parent::appendAttributes($fragment, $component);
        $this->additionalClass('qti-hotspotChoice');
    }
}