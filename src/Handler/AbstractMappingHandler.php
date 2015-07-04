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
        if (is_null($property) || $property === '') {
            return $object;
        }

        $segments = explode('.', $property);
        $name = array_shift($segments);
        $newProperty = implode('.', $segments);

        if (!$reflectionClass->hasProperty($name)) {
            throw new \Exception(sprintf('Unknown property %s on class %s', $name, $reflectionClass->getName()));
        }

        $prop = $reflectionClass->getProperty($name);
        $prop->setAccessible(true);
        $value = $prop->getValue($object);

        if (is_object($value)) {
            $newReflectionClass = new \ReflectionClass($value);
            return $this->getReflectionPropertyValue($newReflectionClass, $value, $newProperty);
        }

        return $value;
    }

    /**
     * Process arguments from Mapping::$arguments
     *
     * @param array|null $arguments
     * @param mixed      $value
     *
     * @return array
     */
    protected function processArguments($arguments, $value = null)
    {
        if (is_null($arguments)) {
            return [ $value ];
        } elseif (!is_array($arguments)) {
            throw new \InvalidArgumentException(
                sprintf('Mapping arguments expected to be an array, got %s', gettype($arguments))
            );
        }

        $args = [];

        foreach ($arguments as $argument) {
            try {
                $value = $this->getReflectionPropertyValue($this->sourceReflection, $this->source, $argument);
            } catch (\Exception $e) { // TODO: Update caught exception
                $value = $argument; // TODO: Fetch value from container
            }

            $args[] = $value;
        }

        return $args;
    }
}
