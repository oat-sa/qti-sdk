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

namespace qtism\data\content;

use InvalidArgumentException;
use qtism\common\utils\Format;
use qtism\data\content\enums\AriaLive;
use qtism\data\content\enums\AriaOrientation;
use qtism\data\QtiComponent;

/**
 * From IMS QTI:
 *
 * The root class of all content objects in the item content model is the bodyElement.
 * It defines a number of attributes that are common to all elements of the content model.
 */
abstract class BodyElement extends QtiComponent
{
    /**
     * From IMS QTI:
     *
     * The id of a body element must be unique within the item.
     *
     * @var string
     * @qtism-bean-property
     */
    private $id = '';

    /**
     * From IMS QTI:
     *
     * Classes can be assigned to individual body elements. Multiple class names can be given.
     * These class names identify the element as being a member of the listed classes. Membership
     * of a class can be used by authoring systems to distinguish between content objects that are
     * not differentiated by this specification. Typically, this information is used to apply
     * different formatting based on definitions in an associated stylesheet, but can also be
     * used for user interface designs that go beyond .
     *
     * @var string
     * @qtism-bean-property
     */
    private $class = '';

    /**
     * From IMS QTI:
     *
     * The main language of the element. This attribute is optional and will usually be
     * inherited from the enclosing element.
     *
     * @var string
     * @qtism-bean-property
     */
    private $lang = '';

    /**
     * From IMS QTI:
     *
     * The label attribute provides authoring systems with a mechanism for labelling elements of
     * the content model with application specific data. If an item uses labels then values for
     * the associated toolName and toolVersion attributes must also be provided.
     *
     * @var string
     * @qtism-bean-property
     */
    private $label = '';

    /**
     * @var string
     * @qtism-bean-property
     */
    private $ariaControls = '';

    /**
     * @var string
     * @qtism-bean-property
     */
    private $ariaDescribedBy = '';

    /**
     * @var string
     * @qtism-bean-property
     */
    private $ariaFlowTo = '';

    /**
     * @var string
     * @qtism-bean-property
     */
    private $ariaLabelledBy = '';

    /**
     * @var string
     * @qtism-bean-property
     */
    private $ariaOwns = '';

    /**
     * @var string
     * @qtism-bean-property
     */
    private $ariaLevel = '';

    /**
     * @var int|bool
     * @qtism-bean-property
     */
    private $ariaLive = false;

    /**
     * @var int|bool
     * @qtism-bean-property
     */
    private $ariaOrientation = false;

    /**
     * @var string
     * @qtism-bean-property
     */
    private $ariaLabel = '';

    /**
     * @var bool
     * @qtism-bean-property
     */
    private $ariaHidden = false;

    /**
     * Create a new BodyElement object.
     *
     * @param string $id A QTI identifier.
     * @param string $class One or more class names separated by spaces.
     * @param string $lang An RFC3066 language.
     * @param string $label A label that does not exceed 256 characters.
     */
    public function __construct($id = '', $class = '', $lang = '', $label = '')
    {
        $this->setId($id);
        $this->setClass($class);
        $this->setLang($lang);
        $this->setLabel($label);
    }

    /**
     * Get the unique identifier of the body element.
     *
     * @return string A QTI identifier.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the unique identifier of the body element.
     *
     * @param string $id A QTI Identifier.
     * @throws InvalidArgumentException If $id is not a valid QTI identifier.
     */
    public function setId($id = '')
    {
        if (is_string($id) && (empty($id) === true || Format::isIdentifier($id, false) === true)) {
            $this->id = $id;
        } else {
            $msg = "The 'id' argument of a body element must be a valid identifier or an empty string";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether a value is defined for the id attribute.
     *
     * @return boolean
     */
    public function hasId()
    {
        return $this->getId() !== '';
    }

    /**
     * Get the classes assigned to the body element.
     *
     * @return string One or more class names separated by spaces.
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the classes assigned to the body element.
     *
     * @param string $class One or more class names separated by spaces.
     * @throws InvalidArgumentException If $class does not represent valid class name(s).
     */
    public function setClass($class = '')
    {
        $class = trim($class);
        if (is_string($class) && (empty($class) === true || Format::isClass($class) === true)) {
            $this->class = $class;
        } else {
            $msg = "The 'class' argument must be a valid class name, '" . $class . "' given";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Wehther a value is defined for the class attribute.
     *
     * @return boolean
     */
    public function hasClass()
    {
        return $this->getClass() !== '';
    }

    /**
     * Get the language of the body element.
     *
     * @return string An RFC3066 language.
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set the language of the body element.
     *
     * @param string $lang An RFC3066 language.
     */
    public function setLang($lang = '')
    {
        $this->lang = $lang;
    }

    /**
     * Whether a value for the lang attribute is defined.
     *
     * @return boolean
     */
    public function hasLang()
    {
        return $this->getLang() !== '';
    }

    /**
     * Get the label of the body element.
     *
     * @return string A string of 256 characters maximum.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label of the body element.
     *
     * @param string $label A string of 256 characters maximum.
     * @throws InvalidArgumentException If $label is not or a string or contains more than 256 characters.
     */
    public function setLabel($label = '')
    {
        if (Format::isString256($label) === true) {
            $this->label = $label;
        } else {
            $msg = "The 'label' argument must be a string that does not exceed 256 characters.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * Whether a value is defined for the label attribute.
     *
     * @return boolean
     */
    public function hasLabel()
    {
        return $this->getLabel() !== '';
    }

    /**
     * @param string $ariaControls
     * @throws InvalidArgumentException
     */
    public function setAriaControls($ariaControls)
    {
        if ($ariaControls !== '' && !Format::isAriaIdRefs($ariaControls)) {
            $val = (is_object($ariaControls)) ? ('instance of ' . get_class($ariaControls)) : $ariaControls;

            $msg = "'${val}' is not a valid value for attribute 'aria-controls'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaControls = $ariaControls;
    }

    /**
     * @return string
     */
    public function getAriaControls()
    {
        return $this->ariaControls;
    }

    /**
     * @return bool
     */
    public function hasAriaControls()
    {
        return $this->ariaControls !== '';
    }

    /**
     * @param string $ariaDescribedBy
     * @throws InvalidArgumentException
     */
    public function setAriaDescribedBy($ariaDescribedBy)
    {
        if ($ariaDescribedBy !== '' && !Format::isAriaIdRefs($ariaDescribedBy)) {
            $val = (is_object($ariaDescribedBy)) ? ('instance of ' . get_class($ariaDescribedBy)) : $ariaDescribedBy;

            $msg = "'${val}' is not a valid value for attribute 'aria-describedby'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaDescribedBy = $ariaDescribedBy;
    }

    /**
     * @return string
     */
    public function getAriaDescribedBy()
    {
        return $this->ariaDescribedBy;
    }

    /**
     * @return bool
     */
    public function hasAriaDescribedBy()
    {
        return $this->ariaDescribedBy !== '';
    }

    /**
     * @param string $ariaFlowTo
     * @throws InvalidArgumentException
     */
    public function setAriaFlowTo($ariaFlowTo)
    {
        if ($ariaFlowTo !== '' && !Format::isAriaIdRefs($ariaFlowTo)) {
            $val = (is_object($ariaFlowTo)) ? ('instance of ' . get_class($ariaFlowTo)) : $ariaFlowTo;

            $msg = "'${val}' is not a valid value for attribute 'aria-flowto'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaFlowTo = $ariaFlowTo;
    }

    /**
     * @return string
     */
    public function getAriaFlowTo()
    {
        return $this->ariaFlowTo;
    }

    /**
     * @return bool
     */
    public function hasAriaFlowTo()
    {
        return $this->ariaFlowTo !== '';
    }

    /**
     * @param string $ariaLabelledBy
     * @throws InvalidArgumentException
     */
    public function setAriaLabelledBy($ariaLabelledBy)
    {
        if ($ariaLabelledBy !== '' && !Format::isAriaIdRefs($ariaLabelledBy)) {
            $val = (is_object($ariaLabelledBy)) ? ('instance of ' . get_class($ariaLabelledBy)) : $ariaLabelledBy;

            $msg = "'${val}' is not a valid value for attribute 'aria-labelledby'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaLabelledBy = $ariaLabelledBy;
    }

    /**
     * @return string
     */
    public function getAriaLabelledBy()
    {
        return $this->ariaLabelledBy;
    }

    /**
     * @return bool
     */
    public function hasAriaLabelledBy()
    {
        return $this->ariaLabelledBy !== '';
    }

    /**
     * @param string $ariaOwns
     * @throws InvalidArgumentException
     */
    public function setAriaOwns($ariaOwns)
    {
        if ($ariaOwns !== '' && !Format::isAriaIdRefs($ariaOwns)) {
            $val = (is_object($ariaOwns)) ? ('instance of ' . get_class($ariaOwns)) : $ariaOwns;

            $msg = "'${val}' is not a valid value for attribute 'aria-owns'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaOwns = $ariaOwns;
    }

    /**
     * @return string
     */
    public function getAriaOwns()
    {
        return $this->ariaOwns;
    }

    /**
     * @return bool
     */
    public function hasAriaOwns()
    {
        return $this->ariaOwns !== '';
    }

    /**
     * @param string $ariaLevel
     * @throws InvalidArgumentException
     */
    public function setAriaLevel($ariaLevel)
    {
        if ($ariaLevel !== '' && !Format::isAriaLevel($ariaLevel)) {
            $val = (is_object($ariaLevel)) ? ('instance of ' . get_class($ariaLevel)) : $ariaLevel;

            $msg = "'${val}' is not a valid value for attribute 'aria-level'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaLevel = (string)$ariaLevel;
    }

    /**
     * @return string
     */
    public function getAriaLevel()
    {
        return $this->ariaLevel;
    }

    /**
     * @return bool
     */
    public function hasAriaLevel()
    {
        return $this->ariaLevel !== '';
    }

    /**
     * @param int $ariaLive A value from the AriaLive enumeration or false for no value.
     * @throws InvalidArgumentException
     */
    public function setAriaLive($ariaLive)
    {
        if ($ariaLive === false || in_array($ariaLive, AriaLive::asArray(), true)) {
            $this->ariaLive = $ariaLive;
        } else {
            $val = (is_object($ariaLive)) ? ('instance of ' . get_class($ariaLive)) : $ariaLive;
            $msg = "'${val}' is not a valid value for attribute 'aria-live'.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @return int
     */
    public function getAriaLive()
    {
        return $this->ariaLive;
    }

    /**
     * @return bool
     */
    public function hasAriaLive()
    {
        return $this->ariaLive !== false;
    }

    /**
     * @param int $ariaOrientation A value from the AriaOrientation enumeration or false for no orientation.
     * @throws InvalidArgumentException
     */
    public function setAriaOrientation($ariaOrientation)
    {
        if ($ariaOrientation === false || in_array($ariaOrientation, AriaOrientation::asArray(), true)) {
            $this->ariaOrientation = $ariaOrientation;
        } else {
            $val = (is_object($ariaOrientation)) ? ('instance of ' . get_class($ariaOrientation)) : $ariaOrientation;
            $msg = "'${val}' is not a valid value for attribute 'aria-orientation'.";
            throw new InvalidArgumentException($msg);
        }
    }

    /**
     * @return false|int A value from the AriaOrientation enumeration or false if not orientation defined.
     */
    public function getAriaOrientation()
    {
        return $this->ariaOrientation;
    }

    /**
     * @return bool
     */
    public function hasAriaOrientation()
    {
        return $this->ariaOrientation !== false;
    }

    /**
     * @param $ariaLabel
     * @throws InvalidArgumentException
     */
    public function setAriaLabel($ariaLabel)
    {
        if (!is_string($ariaLabel)) {
            $val = (is_object($ariaLabel)) ? ('instance of ' . get_class($ariaLabel)) : $ariaLabel;

            $msg = "'${val}' is not a valid value for attribute 'aria-label'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaLabel = $ariaLabel;
    }

    /**
     * @return string
     */
    public function getAriaLabel()
    {
        return $this->ariaLabel;
    }

    /**
     * @return bool
     */
    public function hasAriaLabel()
    {
        return $this->ariaLabel !== '';
    }

    /**
     * @param bool $ariaHidden
     * @throws InvalidArgumentException
     */
    public function setAriaHidden($ariaHidden)
    {
        if (!is_bool($ariaHidden)) {
            $val = (is_object($ariaHidden)) ? ('instance of ' . get_class($ariaHidden)) : $ariaHidden;

            $msg = "'${val}' is not a valid value for attribute 'aria-hidden'.";
            throw new InvalidArgumentException($msg);
        }

        $this->ariaHidden = $ariaHidden;
    }

    /**
     * @return bool
     */
    public function getAriaHidden()
    {
        return $this->ariaHidden;
    }

    /**
     * @return bool
     */
    public function hasAriaHidden()
    {
        return $this->ariaHidden !== false;
    }
}
