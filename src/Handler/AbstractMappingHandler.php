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
        // Handle Doctrine proxies
        if (is_subclass_of($object, 'Doctrine\ORM\Proxy\Proxy') && !$object->__isInitialized()) {
            $object->__load();
        }

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
        } else {
            $prop = $this->getReflectionProperty($reflectionClass, $name);

            if (!$prop) {
                throw new \Exception(sprintf('Unknown property %s on class %s', $name, $reflectionClass->getName()));
            }

            $prop->setAccessible(true);
            $value = $prop->getValue($object);
        }

        if (is_object($value)) {
            $newReflectionClass = new \ReflectionClass($value);
            return $this->getReflectionPropertyValue($newReflectionClass, $value, $newProperty);
        }

        return $value;
    }

    /**
     * Get reflection property
     *
     * @param \ReflectionClass $reflectionClass
     * @param string           $name
     *
     * @return null|\ReflectionProperty
     */
    private function getReflectionProperty(\ReflectionClass $reflectionClass, $name)
    {
        if ($reflectionClass->hasProperty($name)) {
            return $reflectionClass->getProperty($name);
        }

        if ($parent = $reflectionClass->getParentClass()) {
            return $this->getReflectionProperty($parent, $name);
        }

        return null;
    }
}
