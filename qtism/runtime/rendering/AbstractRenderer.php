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

namespace qtism\runtime\rendering;

use qtism\data\QtiComponent;
use \InvalidArgumentException;

/**
 * Interface to implement to pretend to be a class able
 * to render a QtiComponent into another consitution such as
 * XHTML, HTML5, Canvas, ...
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
abstract class AbstractRenderer {
    
    /**
     * The rendering context used by the implementation.
     * 
     * @var AbstractRenderingContext
     */
    private $renderingContext;
    
    /**
     * Create a new AbstractRenderer object.
     * 
     */
    public function __construct() {
        
    }
    
    /**
     * Render a QtiComponent object into another constitution.
     * 
     * @param QtiComponent $component
     * @return mixed The rendered component into another constitution.
     */
    public abstract function render(QtiComponent $component);
    
    /**
     * Set the rendering context for this renderer.
     * 
     * @param AbstractRenderingContext $renderingContext
     */
    public function setRenderingContext(AbstractRenderingContext $renderingContext) {
        $this->renderingContext = $renderingContext;
    }
    
    /**
     * Get the rendering context for this renderer.
     * 
     * @return AbstractRenderingContext
     */
    public function getRenderingContext() {
        return $this->renderingContext;
    }
}