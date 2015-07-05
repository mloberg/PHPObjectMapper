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
use Mlo\ObjectMapper\Handler\ArrayMappingHandler;
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
        if (is_null($annotationReader)) {
            AnnotationRegistry::registerAutoloadNamespace(__NAMESPACE__ . '\Annotation', __DIR__ . '/Annotation');
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
            $handler = new ArrayMappingHandler($source, $target, $this->annotationReader);
        } else {
            $handler = new ObjectMappingHandler($source, $target, $this->annotationReader);
        }

        return $handler->map();
    }
}
