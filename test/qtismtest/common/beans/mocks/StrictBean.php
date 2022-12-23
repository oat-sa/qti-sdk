<?php

namespace qtismtest\common\beans\mocks;

/**
 * Class StrictBean
 */
class StrictBean
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
     * @var bool
     * @qtism-bean-property
     */
    private $cool;

    /**
     * StrictBean constructor.
     *
     * @param $firstName
     * @param $lastName
     * @param $hair
     * @param $cool
     */
    public function __construct($firstName, $lastName, $hair, $cool)
    {
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setHair($hair);
        $this->setCool($cool);
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

    /**
     * @param $cool
     */
    public function setCool($cool): void
    {
        $this->cool = $cool;
    }

    /**
     * @return bool
     */
    public function isCool(): bool
    {
        return $this->cool;
    }
}
