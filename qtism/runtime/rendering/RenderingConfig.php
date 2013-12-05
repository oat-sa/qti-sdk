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

/**
 * Declares how to configure a basic Rendering Engine.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
interface RenderingConfig {
    
    /**
     * Static rendering mode.
     * 
     * @var integer
     */
    const CONTEXT_STATIC = 0;
    
    /**
     * Context-aware rendering.
     * 
     * @var integer
     */
    const CONTEXT_AWARE = 1;
    
    /**
     * Ignore the QTI elements with class name $classes while rendering.
     *
     * @param string|array $classes A QTI class or an array of QTI classes.
     */
    public function ignoreQtiClasses($classes);
    
    /**
     * Get the array containing the QTI class names to be
     * ignored while rendering.
     * 
     * @return array An array of QTI Classes.
     */
    public function getIgnoreClasses();
}