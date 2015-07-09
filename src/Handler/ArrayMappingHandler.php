<?php
/**
 * ArrayMappingHandler.php
 * 
 * @package Mlo\ObjectMapper
 * @subpackage Handler
 */
 
namespace Mlo\ObjectMapper\Handler;

use Doctrine\Common\Annotations\Reader;
use Mlo\ObjectMapper\Annotation\Mapping;

/**
 * ArrayMappingHandler
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class ArrayMappingHandler extends AbstractMappingHandler
{
    /**
     * @var array
     */
    private $source;

    /**
     * @var object
     */
    private $target;

    /**
     * @var \ReflectionClass
     */
    private $targetReflection;

    /**
     * @var string
     */
    private $targetNamespace;

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * Constructor
     *
     * @param array  $source
     * @param object $target
     * @param Reader $annotationReader
     */
    function __construct(array $source, $target, Reader $annotationReader)
    {
        $this->source           = $source;
        $this->target           = $target;
        $this->annotationReader = $annotationReader;

        $this->targetReflection = new \ReflectionClass($target);
        $this->targetNamespace = $this->targetReflection->getNamespaceName();
    }

    /**
     * {@inheritdoc}
     */
    public function map()
    {
        $this->mapProperties();

        return $this->target;
    }

    /**
     * Map object properties
     */
    private function mapProperties()
    {
        foreach ($this->targetReflection->getProperties() as $targetProperty) {
            $this->mapProperty($targetProperty);
        }
    }

    /**
     * Map object property
     *
     * @param \ReflectionProperty $property
     */
    private function mapProperty(\ReflectionProperty $property)
    {
        $annotations = $this->annotationReader->getPropertyAnnotations($property);

        foreach ($annotations as $annotation) {
            if (!$annotation instanceof Mapping) {
                continue;
            }

            $supportedSources = $annotation->getSources();

            if (in_array(Mapping::SOURCE_ALL, $supportedSources) || in_array('array', $supportedSources)) {
                $this->handlePropertyMapping($annotation, $property);
            }
        }
    }

    /**
     * Handle property mapping
     *
     * @param Mapping             $mapping
     * @param \ReflectionProperty $property
     *
     * @throws \Exception If property not found
     */
    private function handlePropertyMapping(Mapping $mapping, \ReflectionProperty $property) {
        $value = null;

        try {
            $value = $this->getArrayValue($this->source, $mapping->getProperty() ?: $property->getName());
        } catch (\Exception $e) { // TODO: Update caught exception
            if (!$mapping->isNullable()) {
                throw $e;
            }
        }

        if ($mapping->getGetter()) {
            // TODO: Throw warning
        }

        if ($mapping->getTarget()) {
            $target = $mapping->getTarget();

            if (!class_exists($target)) {
                $target = $this->targetNamespace . '\\' . $target;
            }

            if (is_null($value) && !$mapping->isNullable()) {
                throw new \Exception(sprintf(
                    'Property %s is null. Set nullable=true to allow null',
                    $property->getName()
                ));
            } elseif (is_array($value)) {
                $targetMapper = new self($value, new $target(), $this->annotationReader);
            } elseif (is_object($value)) {
                $targetMapper = new ObjectMappingHandler($value, new $target(), $this->annotationReader);
            }

            if (isset($targetMapper)) {
                $value = $targetMapper->map();
            }
        }

        if ($mapping->getSetter()) {
            $arguments = $this->processArguments($mapping->getArguments(), $value);
            call_user_func_array([$this->target, $mapping->getSetter()], $arguments);
        } else {
            $property->setAccessible(true);
            $property->setValue($this->target, $value);
        }
    }

    /**
     * Get array value
     *
     * @param array  $array
     * @param string $property
     * @param mixed  $default
     *
     * @return mixed
     * @throws \Exception If property cannot be found
     */
    private function getArrayValue($array, $property, $default = null)
    {
        if (is_null($property) || $property === '') {
            return $array;
        }

        $segments = explode('.', $property);
        $name = array_shift($segments);
        $newProperty = implode('.', $segments);

        if (!isset($array[$name]) && $newProperty) {
            throw new \Exception(sprintf('Unknown property %s in array', $name));
        } elseif (!isset($array[$name])) {
            return $default;
        }

        $value = $array[$name];

        if (is_array($value)) {
            return $this->getArrayValue($value, $newProperty);
        } elseif (is_object($value)) {
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
    private function processArguments($arguments, $value = null)
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
            if (strpos($argument, '$') === 0) {
                $property = substr($argument, 1);
                $value = $this->getArrayValue($this->source, $property);
            } elseif (strpos($argument, '@') === 0) {
                throw new \InvalidArgumentException(sprintf(
                    "Attempted to call method '%s' on array mapping",
                    substr($argument, 1)
                ));
            } else {
                // TODO: Fetch value from container
                $value = $argument;
            }

            $args[] = $value;
        }

        return $args;
    }
}
