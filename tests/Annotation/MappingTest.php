<?php
/**
 * MappingTest.php
 *
 * @package Mlo\ObjectMapper
 * @subpackage Tests
 */

namespace Mlo\ObjectMapper\Tests\Annotation;

use Mlo\ObjectMapper\Annotation\Mapping;

/**
 * MappingTest
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class MappingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set defaults are correctly set
     */
    public function testDefaultsAreCorrectlySet()
    {
        $mapping = new Mapping([]);

        $this->assertEquals([ Mapping::SOURCE_ALL ], $mapping->getSources());
        $this->assertNull($mapping->getProperty());
        $this->assertNull($mapping->getGetter());
        $this->assertNull($mapping->getSetter());
        $this->assertNull($mapping->getArguments());
        $this->assertNull($mapping->getTarget());
        $this->assertFalse($mapping->isNullable());
    }

    /**
     * Sources should be an array, even if just a string is passed
     */
    public function testSourcesConvertsToArray()
    {
        $mapping = new Mapping([
            'value' => 'Test',
        ]);

        $this->assertEquals(['Test'], $mapping->getSources());
    }
}
