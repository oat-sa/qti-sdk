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
use qtism\common\collections\IdentifierCollection;
use qtism\data\content\BodyElement;
use qtism\data\content\FlowCollection;

/**
 * From IMS QTI:
 *
 * In XHTML, table cells are represented by either th or td and these share
 * the following attributes and content model:
 */
abstract class TableCell extends BodyElement
{
    /**
     * The headers of the tableCell.
     *
     * @var IdentifierCollection
     * @qtism-bean-property
     */
    private $headers;

    /**
     * The XHTML scope attribute.
     *
     * @var int
     * @qtism-bean-property
     */
    private $scope = -1;

    /**
     * The XHTML abbr attribute.
     *
     * @var string
     * @qtism-bean-property
     */
    private $abbr = '';

    /**
     * The XHTML axis attribute.
     *
     * @var string
     * @qtism-bean-property
     */
    private $axis = '';

    /**
     * The XHTML rowspan attribute.
     *
     * @var int
     * @qtism-bean-property
     */
    private $rowspan = -1;

    /**
     * The XHTML colspan attribute.
     *
     * @var int
     * @qtism-bean-property
     */
    private $colspan = -1;

    /**
     * The components composing the TableCell.
     *
     * @var FlowCollection
     * @qtism-bean-property
     */
    private $content;

    /**
     * Create a new TableCell object.
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
        $this->setHeaders(new IdentifierCollection());
        $this->setScope(-1);
        $this->setAbbr('');
        $this->setAxis('');
        $this->setRowspan(-1);
        $this->setColspan(-1);
    }

    /**
     * Specify the th element each td element is related to.
     *
     * @param IdentifierCollection $headers A collection of QTI identifiers.
     */
    public function setHeaders(IdentifierCollection $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Get the th element each td element is related to.
     *
     * @return IdentifierCollection A collection of QTI identifiers.
     */
    public function getHeaders(): IdentifierCollection
    {
        return $this->headers;
    }

    /**
     * Whether at least one value is defined for the headers attribute.
     *
     * @return bool
     */
    public function hasHeaders(): bool
    {
        return count($this->getHeaders()) > 0;
    }

    /**
     * Set the scope attribute.
     *
     * @param int $scope A value from the TableCellScope enumeration or -1 if no scope is defined.
     * @throws InvalidArgumentException If $scope is not a value from the TableCellScope enumeration nor -1.
     */
    public function setScope($scope): void
    {
        if (in_array($scope, TableCellScope::asArray(), true) || $scope === -1) {
            $this->scope = $scope;
        } else {
            $msg = "The 'scope' argument must be a value from the TableCellScope enumeration, '" . $scope . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the scope attribute.
     *
     * @return int A value from the TableCellScope enumeration or -1 if no scope is defined.
     */
    public function getScope(): int
    {
        return $this->scope;
    }

    /**
     * Whether a scope is defined.
     *
     * @return bool
     */
    public function hasScope(): bool
    {
        return $this->getScope() !== -1;
    }

    /**
     * Set the value of the abbr attribute.
     *
     * @param string $abbr A string or an empty string if no abbr is defined.
     * @throws InvalidArgumentException If $bbr is not a string.
     */
    public function setAbbr($abbr): void
    {
        if (is_string($abbr)) {
            $this->abbr = $abbr;
        } else {
            $msg = "The 'abbr' attribute must be a string, '" . gettype($abbr) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the value of the abbr attribute.
     *
     * @return string A string or an empty string if no abbr is defined.
     */
    public function getAbbr(): string
    {
        return $this->abbr;
    }

    /**
     * Whether a value for the attribute is defined.
     *
     * @return bool
     */
    public function hasAbbr(): bool
    {
        return $this->getAbbr() !== '';
    }

    /**
     * Set the value of the axis attribute.
     *
     * @param string $axis A string. Give an empty string if no axis is indicated.
     * @throws InvalidArgumentException If $axis is not a string.
     */
    public function setAxis($axis): void
    {
        if (is_string($axis)) {
            $this->axis = $axis;
        } else {
            $msg = "The 'axis' argument must be a string, '" . gettype($axis) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the value of the axis attribute.
     *
     * @return string A string. The string is empty if no axis is defined.
     */
    public function getAxis(): string
    {
        return $this->axis;
    }

    /**
     * Whether a value is defined for the axis attribute.
     *
     * @return bool
     */
    public function hasAxis(): bool
    {
        return $this->getAxis() !== '';
    }

    /**
     * Set the value of the rowspan attribute. Give a negative value if
     * no rowspan attribute is set.
     *
     * @param int $rowspan
     * @throws InvalidArgumentException If $rowspan is not an integer.
     */
    public function setRowspan($rowspan): void
    {
        if (is_int($rowspan)) {
            $this->rowspan = $rowspan;
        } else {
            $msg = "The 'rowspan' argument must be an integer, '" . gettype($rowspan) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the value of the rowspan attribute. A negative value indicates that
     * no rowspan attribute is set.
     *
     * @return int
     */
    public function getRowspan(): int
    {
        return $this->rowspan;
    }

    /**
     * Whether a value for the rowspan attribute is set.
     *
     * @return bool
     */
    public function hasRowspan(): bool
    {
        return $this->getRowspan() >= 0;
    }

    /**
     * Set the colspan attribute. Give a negative integer to indicate that
     * no colspan is set.
     *
     * @param int $colspan An integer.
     * @throws InvalidArgumentException If $colspan is not an integer.
     */
    public function setColspan($colspan): void
    {
        if (is_int($colspan)) {
            $this->colspan = $colspan;
        } else {
            $msg = "The 'colspan' argument must be an integer, '" . gettype($colspan) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the colspan attribute. A negative value indicates that no colspan
     * is set.
     *
     * @return int
     */
    public function getColspan(): int
    {
        return $this->colspan;
    }

    /**
     * Whether a value for the colspan attribute is set.
     *
     * @return bool
     */
    public function hasColspan(): bool
    {
        return $this->getColspan() >= 0;
    }

    /**
     * Set the components composing the TableCell.
     *
     * @param FlowCollection $content A collection of Flow objects.
     */
    public function setContent(FlowCollection $content): void
    {
        $this->content = $content;
    }

    /**
     * Get the components composing the TableCell.
     *
     * @return FlowCollection
     */
    public function getContent(): FlowCollection
    {
        return $this->content;
    }

    /**
     * @return FlowCollection
     */
    public function getComponents(): FlowCollection
    {
        return $this->getContent();
    }
}
