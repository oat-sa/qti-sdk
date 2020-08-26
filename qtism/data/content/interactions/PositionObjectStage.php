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
use qtism\data\content\Block;
use qtism\data\content\xhtml\QtiObject;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The PositionObjectStage QTI class.
 */
class PositionObjectStage extends QtiComponent implements Block
{
    /**
     * From IMS QTI:
     *
     * The image to be used as a stage onto which individual positionObjectInteractions
     * allow the candidate to place their objects.
     *
     * @var QtiObject
     * @qtism-bean-property
     */
    private $object;

    /**
     * The positionObjectInteractions composing the positionObjectStage.
     *
     * @var PositionObjectInteractionCollection
     * @qtism-bean-property
     */
    private $positionObjectInteractions;

    /**
     * Set the image to be used as a stage.
     *
     * @param QtiObject $object An Object object.
     * @qtism-bean-property
     */
    public function setObject(QtiObject $object)
    {
        $this->object = $object;
    }

    /**
     * Get the image to be used as a stage.
     *
     * @return QtiObject An Object object.
     * @qtism-bean-property
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Create a new PositionObjectStage object.
     *
     * @param QtiObject $object The image to be used as a stage.
     * @param PositionObjectInteractionCollection $positionObjectInteractions A collection of PositionObjectInteraction objects.
     */
    public function __construct(QtiObject $object, PositionObjectInteractionCollection $positionObjectInteractions)
    {
        $this->setObject($object);
        $this->setPositionObjectInteractions($positionObjectInteractions);
    }

    /**
     * Set the positionObjectInteractions composing the positionObjectStage.
     *
     * @param PositionObjectInteractionCollection $positionObjectInteractions A collection of PositionObjectInteraction objects.
     * @throws InvalidArgumentException If $positionObjectInteractions is empty.
     */
    public function setPositionObjectInteractions(PositionObjectInteractionCollection $positionObjectInteractions)
    {
        if (count($positionObjectInteractions) > 0) {
            $this->positionObjectInteractions = $positionObjectInteractions;
        } else {
            $msg = 'A PositionObjectStage object must be composed of at least 1 PositionObjectInteraction object, none given.';
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Get the positionObjectInteractions composing the positionObjectStage.
     *
     * @return PositionObjectInteractionCollection A collection of PositionObjectInteraction objects.
     */
    public function getPositionObjectInteractions()
    {
        return $this->positionObjectInteractions;
    }

    /**
     * @return QtiComponentCollection
     */
    public function getComponents()
    {
        return new QtiComponentCollection(array_merge([$this->getObject()], $this->getPositionObjectInteractions()->getArrayCopy()));
    }

    /**
     * @return string
     */
    public function getQtiClassName()
    {
        return 'positionObjectStage';
    }
}
