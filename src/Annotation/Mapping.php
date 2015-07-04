<?php
/**
 * Mapping.php
 * 
 * @package Mlo\ObjectMapper
 * @subpackage Annotation
 */
 
namespace Mlo\ObjectMapper\Annotation;

/**
 * Mapping
 *
 * @Annotation()
 * @Target({"PROPERTY", "METHOD"})
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
class Mapping 
{
    /**
     * Allow all sources
     *
     * @var string
     */
    const SOURCE_ALL = '*';

    /**
     * @var array
     */
    private $sources;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $getter;

    /**
     * @var string
     */
    private $setter;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @var string
     */
    private $target;

    /**
     * @var bool
     */
    private $nullable;

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->sources = isset($params['value']) ? $params['value'] : self::SOURCE_ALL;
        $this->property = isset($params['property']) ? $params['property'] : null;
        $this->getter = isset($params['getter']) ? $params['getter'] : null;
        $this->setter = isset($params['setter']) ? $params['setter'] : null;
        $this->arguments = isset($params['arguments']) ? $params['arguments'] : null;
        $this->target = isset($params['target']) ? $params['target'] : null;
        $this->nullable = isset($params['nullable']) ? $params['nullable'] : false;

        if (!is_array($this->sources)) {
            $this->sources = [ $this->sources ];
        }
    }

    /**
     * Get Sources
     *
     * @return array
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * Get Property
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * Get Getter
     *
     * @return string
     */
    public function getGetter()
    {
        return $this->getter;
    }

    /**
     * Get Setter
     *
     * @return string
     */
    public function getSetter()
    {
        return $this->setter;
    }

    /**
     * Get Arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Get Target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Get Nullable
     *
     * @return boolean
     */
    public function isNullable()
    {
        return $this->nullable;
    }
}
