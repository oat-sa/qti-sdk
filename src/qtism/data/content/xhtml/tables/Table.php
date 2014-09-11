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

use qtism\data\QtiComponentCollection;
use qtism\data\content\FlowStatic;
use qtism\data\content\BlockStatic;
use qtism\data\content\BodyElement;
use \InvalidArgumentException;

/**
 * The XHTML table class.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Table extends BodyElement implements BlockStatic, FlowStatic
{
    /**
     * The base URI of the Table.
     *
     * @var string
     * @qtism-bean-property
     */
    private $xmlBase = '';

    /**
     * The summary attribute.
     *
     * @var string
     * @qtism-bean-property
     */
    private $summary = '';

    /**
     * A caption.
     *
     * @var \qtism\data\content\xhtml\tables\Caption
     * @qtism-bean-property
     */
    private $caption = null;

    /**
     * From IMS QTI:
     *
     * If a table directly contains a col then it must not contain any colgroup elements.
     *
     * @var \qtism\data\content\xhtml\tables\ColCollection
     * @qtism-bean-property
     */
    private $cols;

    /**
     * From IMS QTI:
     *
     * If a table contains a colgroup it must not directly contain any col elements.
     *
     * @var \qtism\data\content\xhtml\tables\ColgroupCollection
     * @qtism-bean-property
     */
    private $colgroups;

    /**
     * A thead.
     *
     * @var Thead
     * @qtism-bean-property
     */
    private $thead = null;

    /**
     * A tfoot.
     *
     * @var Tfoot
     * @qtism-bean-property
     */
    private $tfoot = null;

    /**
     * The tbody elements.
     *
     * @var \qtism\data\content\xhtml\tables\TbodyCollection
     * @qtism-bean-property
     */
    private $tbodies;

    /**
     * Create a new Table object.
     *
     * @param \qtism\data\content\xhtml\tables\TbodyCollection $tbodies A collection of Tbody objects.
     * @param string $id The id of the bodyElement.
     * @param string $class The class of the bodyElement.
     * @param string $lang The language of the bodyElement.
     * @param string $label The label of the bodyElement.
     * @throws \InvalidArgumentException If any of arguments is invalid.
     */
    public function __construct(TbodyCollection $tbodies, $id = '', $class = '', $lang = '', $label = '')
    {
        parent::__construct($id, $class, $lang, $label);
        $this->setTbodies($tbodies);
        $this->setColgroups(new ColgroupCollection());
        $this->setCols(new ColCollection());
    }

    /**
     * Set the value of the summary attribute. An empty string
     * means there is no summary.
     *
     * @param string $summary
     * @throws \InvalidArgumentException If $summary is not a string.
     */
    public function setSummary($summary)
    {
        if (is_string($summary) === true) {
            $this->summary = $summary;
        } else {
            $msg = "The 'summary' argument must be a string, '" . gettype($summary) . "' given.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the value of the summary attribute. An empty string means there is
     * no summary.
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Wheter a value for the summary attribute is defined.
     *
     * @return boolean
     */
    public function hasSummary()
    {
        return $this->getSummary() !== '';
    }

    /**
     * Set the Caption object of the Table. A null value means there
     * is no Caption.
     *
     * @param \qtism\data\content\xhtml\tables\Caption $caption A Caption object or null.
     */
    public function setCaption(Caption $caption = null)
    {
        $this->caption = $caption;
    }

    /**
     * Get the Caption object of the Table. A null value means there
     * is no Caption.
     *
     * @return \qtism\data\content\xhtml\tables\Caption|null A Caption object or null.
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Wheter the Table contains a Caption object.
     *
     * @return boolean
     */
    public function hasCaption()
    {
        return $this->getCaption() !== null;
    }

    /**
     * Set the Col objects composing the Table.
     *
     * @param \qtism\data\content\xhtml\tables\ColCollection $cols A collection of Col objects.
     */
    public function setCols(ColCollection $cols)
    {
        $this->cols = $cols;
    }

    /**
     * Get the Col objects composing the Table.
     *
     * @return \qtism\data\content\xhtml\tables\ColCollection A collection of Col objects.
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * Set the Colgroup objects composing the Table.
     *
     * @param \qtism\data\content\xhtml\tables\ColgroupCollection $colgroups A collection of Colgroup objects.
     */
    public function setColgroups(ColgroupCollection $colgroups)
    {
        $this->colgroups = $colgroups;
    }

    /**
     * Get the Colgroup objects composing the Table.
     *
     * @return \qtism\data\content\xhtml\tables\ColgroupCollection A collection of Colgroup objects.
     */
    public function getColgroups()
    {
        return $this->colgroups;
    }

    /**
     * Set the Thead object. A null value means there is no
     * Thead.
     *
     * @param \qtism\data\content\xhtml\tables\Thead $thead A Thead object or null.
     */
    public function setThead(Thead $thead = null)
    {
        $this->thead = $thead;
    }

    /**
     * Get the Thead object. A null value means there is no Thead.
     *
     * @return \qtism\data\content\xhtml\tables\Thead A Thead object or null.
     */
    public function getThead()
    {
        return $this->thead;
    }

    /**
     * Whether the Table contains a Thead object.
     *
     * @return boolean
     */
    public function hasThead()
    {
        return $this->getThead() !== null;
    }

    /**
     * Set the Tfoot object
     *
     * @param \qtism\data\content\xhtml\tables\Tfoot $tfoot
     */
    public function setTfoot(Tfoot $tfoot)
    {
        $this->tfoot = $tfoot;
    }

    /**
     * Get the Tfoot object of the Table. A null value means there is no
     * Tfoot.
     *
     * @return \qtism\data\content\xhtml\tables\Tfoot A Tfoot object or null.
     */
    public function getTfoot()
    {
        return $this->tfoot;
    }

    /**
     * Whether the Table contains a Tfoot object.
     *
     * @return boolean
     */
    public function hasTfoot()
    {
        return $this->getTfoot() !== null;
    }

    /**
     * Set the Tbody objects composing the Table.
     *
     * @param \qtism\data\content\xhtml\tables\TbodyCollection $tbodies A collection of Tbody objects.
     */
    public function setTbodies(TbodyCollection $tbodies)
    {
        $this->tbodies = $tbodies;
    }

    /**
     * Get the Tbody objects composing the Table.
     *
     * @return \qtism\data\content\xhtml\tables\TbodyCollection A collection of Tbody objects.
     */
    public function getTbodies()
    {
        return $this->tbodies;
    }

    /**
     * Set the base URI of the Table.
     *
     * @param string $xmlBase A URI.
     * @throws \InvalidArgumentException if $base is not a valid URI nor an empty string.
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
     * @see \qtism\data\content\Flow::getXmlBase()
     */
    public function getXmlBase()
    {
        return $this->xmlBase;
    }

    /**
     * @see \qtism\data\content\Flow::hasXmlBase()
     */
    public function hasXmlBase()
    {
        return $this->getXmlBase() !== '';
    }

    /**
     * @see \qtism\data\QtiComponent::getComponents()
     */
    public function getComponents()
    {
        $array = array();

        if ($this->hasCaption() === true) {
            $array[] = $this->getCaption();
        }

        $array = array_merge($array, $this->getCols()->getArrayCopy(), $this->getColgroups()->getArrayCopy());

        if ($this->hasThead() === true) {
            $array[] = $this->getThead();
        }

        if ($this->hasTfoot() === true) {
            $array[] = $this->getTfoot();
        }

        $array = array_merge($array, $this->getTbodies()->getArrayCopy());

        return new QtiComponentCollection($array);
    }

    /**
     * @see \qtism\data\QtiComponent::getQtiClassName()
     */
    public function getQtiClassName()
    {
        return 'table';
    }
}
