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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\xhtml\tables;

use qtism\data\content\BodyElement;
use \InvalidArgumentException;

/**
 * The Tfoot XHTML class.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Tfoot extends BodyElement {
    
    /**
     * The Tr objects composing the Tfoot.
     * 
     * @var \qtism\data\content\xhtml\tables\TrCollection
     * @qtism-bean-property
     */
    private $content;
    
    /**
     * Create a new Tfoot object.
     * 
     * @param \qtism\data\content\xhtml\tables\TrCollection $content A collection of Tr objects with at least one Tr object.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The lang of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws \InvalidArgumentException If any of the arguments above is invalid.
     */
    public function __construct(TrCollection $content, $id = '', $class = '', $lang = '', $label = '') {
        parent::__construct($id, $class, $lang, $label);
        $this->setContent($content);
    }
    
    /**
     * Set the Tr objects composing the Tfoot.
     * 
     * @param \qtism\data\content\xhtml\tables\TrCollection $content A collection of Tfoot object.
     * @throws \InvalidArgumentException If $content is empty.
     */
    public function setContent(TrCollection $content) {
        if (count($content) > 0) {
            $this->content = $content;
        }
        else {
            $msg = "A Tfoot object must be composed of at least 1 Tr object, none given.";
            throw new InvalidArgumentException($msg);
        }
    }
    
    /**
     * Get the Tr objects composing the Tfoot.
     * 
     * @return \qtism\data\content\xhtml\tables\TrCollection
     */
    public function getContent() {
        return $this->content;
    }
    
    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents() {
        return $this->getContent();
    }
    
    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName() {
        return 'tfoot';
    }
}
