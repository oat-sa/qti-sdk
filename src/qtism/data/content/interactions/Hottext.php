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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\content\interactions;

use InvalidArgumentException;
use qtism\data\content\FlowStatic;
use qtism\data\content\FlowTrait;
use qtism\data\content\InlineStatic;
use qtism\data\content\InlineStaticCollection;

/**
 * From IMS QTI:
 *
 * A hottext area is used within the content of an hottextInteraction to provide
 * the individual choices. It must not contain any nested interactions or other
 * hottext areas.
 *
 * When a hottext choice is hidden (by the value of an associated template variable)
 * the content of the choice must still be presented to the candidate as if it were
 * simply part of the surrounding material. In the case of hottext, the effect of
 * hiding the choice is simply to make the run of text unselectable by the candidate.
 */
class Hottext extends Choice implements FlowStatic, InlineStatic
{
    use FlowTrait;

    /**
     * The components composing the hottext.
     *
     * @var InlineStaticCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new Hottext object.
     *
     * @param string $identifier The identifier of the choice.
     * @param string $id The id of the bodyElement
     * @param string $class The class of the bodyElement.
     * @param string $lang The lang of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the argument is invalid.
     */
    public function __construct($identifier, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($identifier, $id, $class, $lang, $label);
        $this->setContent(new InlineStaticCollection());
    }

    /**
     * Set the components composing the hottext.
     *
     * @param InlineStaticCollection $content A collection of InlineStatic objects.
     */
    public function setContent(InlineStaticCollection $content)
    {
        $this->content = $content;
    }

    /**
     * Get the components composing the hottext.
     *
     * @return InlineStaticCollection
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        return $this->getContent();
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'hottext';
    }
}
