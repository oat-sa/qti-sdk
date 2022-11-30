<?php

declare(strict_types=1);

namespace qtismtest\common\beans\mocks;

/**
 * Class NotStrictConstructorBean
 */
class NotStrictConstructorBean
{
    /**
     * @var string
     * @qtism-bean-property
     */
    private $firstName;

    /**
     * @var string
     * @qtism-bean-property
     */
    private $lastName;

    /**
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
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName(): mixed
    {
        return $this->girstName;
    }

    /**
     * @param $lastName
     * @return string
     */
    public function setLastName($lastName): string
    {
        $this->lastName = $lastName;

        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param $hair
     */
    public function setHair($hair): void
    {
        $this->hair = $hair;
    }

    /**
     * @return string
     */
    public function getHair(): string
    {
        return $this->hair;
    }
}
