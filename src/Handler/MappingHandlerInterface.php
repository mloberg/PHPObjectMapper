<?php
/**
 * MappingHandlerInterface.php
 * 
 * @package Mlo\ObjectMapper
 * @subpackage Handler
 */
 
namespace Mlo\ObjectMapper\Handler;

/**
 * MappingHandlerInterface
 *
 * @author Matthew Loberg <loberg.matt@gmail.com>
 */
interface MappingHandlerInterface
{
    /**
     * Do object mapping
     *
     * @return mixed Mapped object
     */
    public function map();
}
