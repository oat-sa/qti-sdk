<?php

declare(strict_types=1);

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

namespace qtism\data\content\xhtml\presentation;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\BlockStatic;
use qtism\data\content\BodyElement;
use qtism\data\content\FlowStatic;
use qtism\data\QtiComponentCollection;

/**
 * The hr XHTML class.
 */
class Hr extends BodyElement implements BlockStatic, FlowStatic
{
    /**
     * The base URI of the Hr.
     *
     * @var string
     * @qtism-bean-property
     */
    private $xmlBase = '';

    /**
     * Create a new Hr object.
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
    }

    /**
     * Set the base URI of the Hr.
     *
     * @param string $xmlBase A URI.
     * @throws InvalidArgumentException if $base is not a valid URI nor an empty string.
     */
    public function setXmlBase($xmlBase = ''): void
    {
        if (is_string($xmlBase) && (empty($xmlBase) || Format::isUri($xmlBase))) {
            $this->xmlBase = $xmlBase;
        } else {
            $msg = "The 'xmlBase' argument must be an empty string or a valid URI, '" . $xmlBase . "' given";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the base URI of the Hr.
     *
     * @return string An empty string or a URI.
     */
    public function getXmlBase(): string
    {
        return $this->xmlBase;
    }

    /**
     * @return bool
     */
    public function hasXmlBase(): bool
    {
        return $this->getXmlBase() !== '';
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents(): QtiComponentCollection
    {
        return new QtiComponentCollection();
    }

    /**
     * @return string
     */
    public function getQtiClassName(): string
    {
        return 'hr';
    }
}
