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

namespace qtism\data\content\xhtml\text;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\BlockStatic;
use qtism\data\content\BodyElement;
use qtism\data\content\FlowCollection;
use qtism\data\content\FlowStatic;

/**
 * The XHTML div class.
 */
class Div extends BodyElement implements BlockStatic, FlowStatic
{
    /**
     * A base URI.
     *
     * @var string
     * @qtism-bean-property
     */
    private $xmlBase = '';

    /**
     * The Flow objects composing the Div.
     *
     * @var FlowCollection A collection of Flow objects.
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new Div object.
     *
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws InvalidArgumentException If one of the arguments is invalid.
     */
    public function __construct($id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setContent(new FlowCollection());
    }

    /**
     * Get the collection of Flow objects composing the Div.
     *
     * @return FlowCollection A collection of Flow objects.
     */
    public function getComponents()
    {
        return $this->getContent();
    }

    /**
     * Set the collection of Flow objects composing the Div.
     *
     * @param FlowCollection $content A collection of Flow objects.
     */
    public function setContent(FlowCollection $content)
    {
        $this->content = $content;
    }

    /**
     * Get the collection of Flow objects composing the Div.
     *
     * @return FlowCollection
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the base URI of the Div.
     *
     * @param string $xmlBase A URI.
     * @throws InvalidArgumentException if $base is not a valid URI nor an empty string.
     */
    public function setXmlBase($xmlBase = '')
    {
        if (is_string($xmlBase) && (empty($xmlBase) || Format::isUri($xmlBase))) {
            $this->xmlBase = $xmlBase;
        } else {
            $msg = "The 'xmlBase' argument must be an empty string or a valid URI, '" . $xmlBase . "' given";
            throw new InvalidArgumentException($msg);
        }
    }

    public function getXmlBase()
    {
        return $this->xmlBase;
    }

    public function hasXmlBase()
    {
        return $this->getXmlBase() !== '';
    }

    public function getQtiClassName()
    {
        return 'div';
    }
}
