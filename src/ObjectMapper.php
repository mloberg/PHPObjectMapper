<?php
/**
 * ObjectMapper.php
 * 
 * @package Mlo\ObjectMapper
 */
 
namespace Mlo\ObjectMapper;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use Mlo\ObjectMapper\Handler\ObjectMappingHandler;

/**
 * ObjectMapper
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class ObjectMapper
{
    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * Constructor
     *
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader = null)
    {
        // TODO: Better Annotation Registry setup
        if (is_null($annotationReader)) {
            AnnotationRegistry::registerFile(__DIR__.'/Annotation/Mapping.php');
            $annotationReader = new AnnotationReader();
        }

        $this->annotationReader = $annotationReader;
    }

    /**
     * Map an object to another object
     *
     * @param array|object $source
     * @param string|object $target
     *
     * @return mixed
     */
    public function map($source, $target)
    {
        if (is_string($target)) {
            $target = new $target();
        }

        if (is_array($source)) {
            trigger_error("Array mapping not yet implemented", E_WARNING);
            return $target;
        }

        $handler = new ObjectMappingHandler($source, $target, $this->annotationReader);

        return $handler->map();
    }
}
