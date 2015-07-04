<?php
/**
 * ArrayMappingHandlerTest.php
 *
 * @package Mlo\ObjectMapper
 * @subpackage Tests
 */

namespace Mlo\ObjectMapper\Tests\Handler;

use Doctrine\Common\Annotations\AnnotationReader;
use Mlo\ObjectMapper\Handler\ArrayMappingHandler;

/**
 * ArrayMappingHandlerTest
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class ArrayMappingHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Attempted to call method 'foo'
     */
    public function testExceptionThrownOnMethodArgument()
    {
        $arrayMappingHandler = new ArrayMappingHandler([], new \stdClass(), new AnnotationReader());

        $reflectionClass = new \ReflectionClass($arrayMappingHandler);

        $reflectionMethod = $reflectionClass->getMethod('processArguments');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($arrayMappingHandler, ['@foo']);
    }
}
