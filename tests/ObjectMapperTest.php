<?php
/**
 * ObjectMapperTest.php
 * 
 * @package Mlo\ObjectMapper
 * @subpackage Tests
 */
 
namespace Mlo\ObjectMapper\Tests;

use Mlo\ObjectMapper\ObjectMapper;
use Mlo\ObjectMapper\Tests\Mock\Contact;
use Mlo\ObjectMapper\Tests\Mock\Person;

/**
 * ObjectMapperTest
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class ObjectMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic object mapping
     */
    public function testObjectMapping()
    {
        $person = (new Person())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setContact(
                (new Contact())
                    ->setEmail('jdoe@example.com')
                    ->setPhone('555-555-5555')
                    ->setAddress1('555 Main St')
                    ->setCity('Minneapolis')
                    ->setState('MN')
                    ->setZipCode(55555)
            )
        ;

        $mapper = new ObjectMapper();

        /** @var Mock\Employee $employee */
        $employee = $mapper->map($person, 'Mlo\ObjectMapper\Tests\Mock\Employee');

        $this->assertInstanceOf('Mlo\ObjectMapper\Tests\Mock\Employee', $employee);
        $this->assertInstanceOf('Mlo\ObjectMapper\Tests\Mock\Address', $employee->getAddress());

        // Assert getters and setters work
        $this->assertEquals($person->getFirstName(), $employee->getFirstName());

        // Assert properties with same names map and can map by properties
        $this->assertEquals($person->getLastName(), $employee->getLastName());

        // Assert mapping from a dot-notation path works
        $this->assertEquals($person->getContact()->getEmail(), $employee->getEmail());

        // Assert things aren't automatically mapped
        $this->assertNull($employee->getAddress()->getCountry());
    }
}
