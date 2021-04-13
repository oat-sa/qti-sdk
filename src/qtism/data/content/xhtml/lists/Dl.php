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

namespace qtism\data\content\xhtml\lists;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\BlockStatic;
use qtism\data\content\BodyElement;
use qtism\data\content\FlowStatic;
use qtism\data\QtiComponentCollection;

/**
 * The dl XHTML class.
 */
class Dl extends BodyElement implements BlockStatic, FlowStatic
{
    /**
     * The base URI of the Dl.
     *
     * @var string
     * @qtism-bean-property
     */
    private $xmlBase = '';

    /**
     * The DlElement objects composing the Dl.
     *
     * @var DlElementCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new Dl object.
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
        $this->setContent(new DlElementCollection());
    }

    /**
     * Set the DlElement objects composing the Dl.
     *
     * @param DlElementCollection $content A collection of DlElement objects.
     */
    public function setContent(DlElementCollection $content)
    {
        $this->content = $content;
    }

    /**
     * Get the DlElement objects composing the Dl.
     *
     * @return DlElementCollection
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the DlElement objects composing the Dl.
     *
     * @return QtiComponentCollection A collection of DlElement objects.
     */
    public function getComponents()
    {
        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'dl';
    }

    /**
     * Set the base URI of the Dl.
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

    /**
     * Get the base URI of the Dl.
     *
     * @return string An empty string or a URI.
     */
    public function getXmlBase()
    {
        return $this->xmlBase;
    }

    /**
     * @return bool
     */
    public function hasXmlBase()
    {
        return $this->getXmlBase() !== '';
    }
}
