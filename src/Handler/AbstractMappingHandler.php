<?php
/**
 * AbstractMappingHandler.php
 * 
 * @package Mlo\ObjectMapper
 * @subpackage Handler
 */
 
namespace Mlo\ObjectMapper\Handler;

/**
 * AbstractMappingHandler
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
abstract class AbstractMappingHandler implements MappingHandlerInterface
{
    /**
     * Get property value
     *
     * @param \ReflectionClass $reflectionClass
     * @param object           $object
     * @param string           $property
     *
     * @return mixed
     * @throws \Exception If property cannot be found
     */
    protected function getReflectionPropertyValue(\ReflectionClass $reflectionClass, $object, $property)
    {
        if ($property === '#self') {
            return $object;
        }

        if (is_null($property) || $property === '') {
            return $object;
        }

        $segments = explode('.', $property);
        $name = array_shift($segments);
        $newProperty = implode('.', $segments);

        if (strpos($name, '@') === 0) {
            $method = substr($name, 1);
            $value = call_user_func([$object, $method]);
        } elseif ($reflectionClass->hasProperty($name)) {
            $prop = $reflectionClass->getProperty($name);
            $prop->setAccessible(true);
            $value = $prop->getValue($object);
        } else {
            throw new \Exception(sprintf('Unknown property %s on class %s', $name, $reflectionClass->getName()));
        }

        if (is_object($value)) {
            $newReflectionClass = new \ReflectionClass($value);
            return $this->getReflectionPropertyValue($newReflectionClass, $value, $newProperty);
        }

        return $value;
    }
}
