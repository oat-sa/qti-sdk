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

use qtism\runtime\common\State;
use qtism\data\ViewCollection;

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
    
    /**
     * Set the policy ruling the way qti:choice components are managed while rendering.
     * 
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:choice is described in QTI-XML. The component will never be discarded from rendering.
     * * In CONTEXT_AWARE mode, the component will be rendered as an element or discarded from rendering depending on the value of the variable referenced by the choice:templateIdentifier attribute and the value of the choice:showHide attribute.
     * 
     * @param integer $policy RenderingConfig::CONTEXT_STATIC or RenderingConfig::CONTEXT_AWARE.
     * @see http://www.imsglobal.org/question/qtiv2p1/imsqti_infov2p1.html#element10271 The qti:choice class.
     */
    public function setChoiceShowHidePolicy($policy);
    
    /**
     * Get the policy ruling the way qti:hoice components are managed while rendering.
     * 
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:choice is described in QTI-XML. The component will never be discarded from rendering.
     * * In CONTEXT_AWARE mode, the component will be rendered as an element or discarded from rendering depending on the value of the variable referenced by the choice:templateIdentifier attribute and the value of the choice:showHide attribute.
     * 
     * @return integer RenderingConfig::CONTEXT_STATIC or RenderingConfig::CONTEXT_AWARE.
     * @see http://www.imsglobal.org/question/qtiv2p1/imsqti_infov2p1.html#element10271 The qti:choice class.
     */
    public function getChoiceShowHidePolicy();
    
    /**
     * Set the policy ruling the way qti:feedbackElement are managed while rendering.
     * 
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:feedbackElement is defined. It will never be discarded from the final rendering.
     * * In CONTEXT_AWARE moden, the component will be rendered as an element or discarded from the final rendering depending on the value of the variable referenced by the qti:feedbackElement.
     * 
     * @param integer $policy RenderingConfig::CONTEXT_STATIC or RenderingConfig::CONTEXT_AWARE.
     */
    public function setFeedbackShowHidePolicy($policy);
    
    /**
     * Get the policy ruling the way qti:feedbackElement are managed while rendering.
     * 
     * * In CONTEXT_STATIC mode, the qti-show/qti-hide classes will be set on the rendered element depending on how the qti:feedbackElement is defined. It will never be discarded from the final rendering.
     * * In CONTEXT_AWARE moden, the component will be rendered as an element or discarded from the final rendering depending on the value of the variable referenced by the qti:feedbackElement.
     * 
     * @return integer RenderingConfig::CONTEXT_STATIC or RenderingConfig::CONTEXT_AWARE.
     */
    public function getFeedbackShowHidePolicy();
    
    /**
     * Set the policy ruling the way QTI components with a qti:view attribute are managed during the rendering phase.
     * 
     * * In CONTEXT_STATIC mode, the qti-view-candidate|qti-view-auhor|qti-view-proctor|qti-view-tutor|qti-view-tutor|qti-view-testConstructor|qti-view-scorer CSS class will be simply added to the rendered elements.
     * * In CONTEXT_STATIC mode, CSS classes will be set up as in CONTEXT_STATIC mode, but views that do not match the view given by the client-code will be discarded from rendering.
     * 
     * @param integer $policy RenderingConfig::CONTEXT_STATIC or RenderingConfig::CONTEXT_AWARE.
     */
    public function setViewPolicy($policy);
    
    /**
     * Set the policy ruling the way QTI components with a qti:view attribute are managed during the rendering phase.
     * 
     * * In CONTEXT_STATIC mode, the qti-view-candidate|qti-view-auhor|qti-view-proctor|qti-view-tutor|qti-view-tutor|qti-view-testConstructor|qti-view-scorer CSS class will be simply added to the rendered elements.
     * * In CONTEXT_STATIC mode, CSS classes will be set up as in CONTEXT_STATIC mode, but views that do not match the view given by the client-code will be discarded from rendering.
     * 
     * @return integer RenderingConfig::CONTEXT_STATIC or RenderingConfig::CONTEXT_AWARE.
     */
    public function getViewPolicy();
    
    /**
     * Set the contextual qti:view(s) to be used in CONTEXT_AWARE mode.
     * 
     * @param ViewCollection $views A collection of values from the View enumeration.
     */
    public function setViews(ViewCollection $views);
    
    /**
     * Get the contextual qti:view to be used in CONTEXT_AWARE mode.
     * 
     * @return ViewCollection A collection of values from the View enumeration.
     */
    public function getViews();
    
    /**
     * Set the State to be used as the context used in CONTEXT_AWARE mode.
     * 
     * @param State $state A State object.
     */
    public function setState(State $state);
    
    /**
     * Get the State used in CONTEXT_AWARE mode.
     * 
     * @return State A State object.
     */
    public function getState();
}