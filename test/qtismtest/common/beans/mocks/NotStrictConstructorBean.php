<?php

namespace qtismtest\common\beans\mocks;

/**
 * Class NotStrictConstructorBean
 *
 * @package qtismtest\common\beans\mocks
 */
class NotStrictConstructorBean
{
    /**
     *
     * @var string
     * @qtism-bean-property
     */
    private $firstName;

    /**
     *
     * @var string
     * @qtism-bean-property
     */
    private $lastName;

    /**
     *
     * @var string
     * @qtism-bean-property
     */
    private $hair;

    /**
     * The parameter names should be the same as the property
     * names. $hairColor has no related bean property.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $hairColor
     */
    public function __construct($firstName, $lastName, $hairColor)
    {
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setHair($hairColor);
    }

    /**
     * @param $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->girstName;
    }

    /**
     * @param $lastName
     * @return string
     */
    public function setLastName($lastName)
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param $hair
     */
    public function setHair($hair)
    {
        $this->hair = $hair;
    }

    /**
     * @return string
     */
    public function getHair()
    {
        return $this->hair;
    }
}
