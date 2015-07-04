<?php
/**
 * Employee.php
 * 
 * @package Mlo\ObjectMapper
 * @subpackage Tests
 */
 
namespace Mlo\ObjectMapper\Tests\Mock;

use Mlo\ObjectMapper\Annotation\Mapping;

/**
 * Employee
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class Employee 
{
    /**
     * @var int
     */
    private $id;

    /**
     * @Mapping({"Person", "array"}, getter="getFirstName", setter="setFirstName")
     * @var string
     */
    private $firstName;

    /**
     * @Mapping()
     * @var string
     */
    private $lastName;

    /**
     * @Mapping({"Person", "array"}, property="contact.email")
     * @var string
     */
    private $email;

    /**
     * @Mapping({"Person", "array"}, property="contact.phone")
     * @var string
     */
    private $phone;

    /**
     * @Mapping({"Person", "array"}, property="contact", target="Address")
     * @var Address
     */
    private $address;

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Id
     *
     * @param int $id
     *
     * @return Employee
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get FirstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set FirstName
     *
     * @param string $firstName
     *
     * @return Employee
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get LastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set LastName
     *
     * @param string $lastName
     *
     * @return Employee
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set Email
     *
     * @param string $email
     *
     * @return Employee
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get Phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set Phone
     *
     * @param string $phone
     *
     * @return Employee
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get Address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set Address
     *
     * @param Address $address
     *
     * @return Employee
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
        return $this;
    }
}
