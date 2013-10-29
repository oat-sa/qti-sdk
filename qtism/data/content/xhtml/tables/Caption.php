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

namespace qtism\data\content\xhtml\tables;

use qtism\data\content\InlineCollection;
use qtism\data\content\BodyElement;
use \InvalidArgumentException;

/**
 * The XHTML caption class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Caption extends BodyElement {
    
    /**
     * The components composing the caption.
     * 
     * @var InlineCollection
     * @qtism-bean-property
     */
    private $components;
    
    /**
     * Create a new Caption object.
     * 
     * @param unknown_type $id The id of the bodyElement.
     * @param unknown_type $class The class of the bodyElement.
     * @param unknown_type $lang The language of the bodyElement.
     * @param unknown_type $label The label of the bodyElement;
     * @throws InvalidArgumentException If one of the arguments is invalid.
     */
    public function __construct($id = '', $class = '', $lang = '', $label = '') {
        parent::__construct($id, $class, $lang, $label);
        $this->setComponents(new InlineCollection());
    }
    
    /**
     * Get the components composing the caption.
     * 
     * @return InlineCollection A collection of Inline objects.
     */
    public function getComponents() {
        return $components;
    }
    
    /**
     * Set the components composing the caption.
     * 
     * @param InlineCollection $components A collection of Inline objects.
     */
    public function setComponents(InlineCollection $components) {
        $this->components = $components;
    }
    
    public function getQtiClassName() {
        return 'caption';
    }
}