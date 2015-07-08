<?php
/**
 * MappingHandler.php
 * 
 * @package Mlo\ObjectMapper
 * @subpackage Handler
 */
 
namespace Mlo\ObjectMapper\Handler;

use Doctrine\Common\Annotations\Reader;
use Mlo\ObjectMapper\Annotation\Mapping;

/**
 * MappingHandler
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class ObjectMappingHandler extends AbstractMappingHandler
{
    /**
     * @var object
     */
    private $source;

    /**
     * @var \ReflectionClass
     */
    private $sourceReflection;

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
     * @param object $source
     * @param object $target
     * @param Reader $annotationReader
     */
    public function __construct($source, $target, Reader $annotationReader)
    {
        $this->source = $source;
        $this->target = $target;
        $this->annotationReader = $annotationReader;

        $this->sourceReflection = new \ReflectionClass($this->source);
        $this->targetReflection = new \ReflectionClass($this->target);
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

            $handlers = array_filter($annotation->getSources(), function ($type) {
                if ($type === Mapping::SOURCE_ALL) {
                    return true;
                }

                if (!class_exists($type)) {
                    $type = $this->targetNamespace . '\\' . $type;
                }

                return ($this->source instanceof $type);
            });

            if (!empty($handlers)) {
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
    private function handlePropertyMapping(Mapping $mapping, \ReflectionProperty $property)
    {
        $value = null;

        try {
            $value = $this->getReflectionPropertyValue(
                $this->sourceReflection,
                $this->source,
                $mapping->getProperty() ?: $property->getName()
            );
        } catch (\Exception $e) { // TODO: Update caught exception
            if (!$mapping->isNullable() && !$mapping->getArguments() && !$mapping->getGetter()) {
                throw $e;
            }
        }

        if ($mapping->getGetter()) {
            $source = $mapping->getProperty() ? $value : $this->source;
            $value = call_user_func([$source, $mapping->getGetter()]);
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
                $targetMapper = new ArrayMappingHandler($value, new $target(), $this->annotationReader);
            } elseif (is_object($value)) {
                $targetMapper = new self($value, new $target(), $this->annotationReader);
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
                $value = $this->getReflectionPropertyValue($this->sourceReflection, $this->source, $property);
            } elseif (strpos($argument, '@') === 0) {
                $method = substr($argument, 1);
                $value = call_user_func([$this->source, $method]);
            } else {
                // TODO: Fetch value from container
                $value = $argument;
            }

            $args[] = $value;
        }

        return $args;
    }
}
