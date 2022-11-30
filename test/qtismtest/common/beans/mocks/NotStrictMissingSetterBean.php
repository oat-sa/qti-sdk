<?php

declare(strict_types=1);

namespace qtismtest\common\beans\mocks;

/**
 * Class NotStrictMissingSetterBean
 */
class NotStrictMissingSetterBean
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
     * NotStrictMissingSetterBean constructor.
     *
     * @param $firstName
     * @param $lastName
     * @param $hair
     */
    public function __construct($firstName, $lastName, $hair)
    {
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setHair($hair);
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
     * The setHair method should be public.
     *
     * @param string $hair
     */
    protected function setHair($hair): void
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
