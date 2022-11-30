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

namespace qtism\data\content\xhtml\tables;

use InvalidArgumentException;
use qtism\data\content\BodyElement;
use qtism\data\QtiComponentCollection;

/**
 * The col XHTML class.
 */
class Col extends BodyElement
{
    /**
     * The span attribute.
     *
     * @var int
     * @qtism-bean-property
     */
    private $span = 1;

    /**
     * Create a new Col object.
     *
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The language of the bodyElement.
     * @throws InvalidArgumentException If one of the argument is invalid.
     */
    public function __construct($id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setSpan(1);
    }

    /**
     * Set the span attribute.
     *
     * @param int $span A strictly positive integer.
     * @throws InvalidArgumentException If $span is not a positive integer.
     */
    public function setSpan($span): void
    {
        if (is_int($span) && $span > 0) {
            $this->span = $span;
        } else {
            $msg = "The 'span' attribute must be a strictly positive (> 0) integer, '" . gettype($span) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the span attribute.
     *
     * @return int A strictly positive integer.
     */
    public function getSpan(): int
    {
        return $this->span;
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
        return 'col';
    }
}
