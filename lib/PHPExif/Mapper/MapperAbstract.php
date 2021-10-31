<?php
/**
 * PHP Exif Mapper Interface: Defines the interface for data mappers
 *
 * @link        http://github.com/miljar/PHPExif for the canonical source repository
 * @copyright   Copyright (c) 2015 Tom Van Herreweghe <tom@theanalogguy.be>
 * @license     http://github.com/miljar/PHPExif/blob/master/LICENSE MIT License
 * @category    PHPExif
 * @package     Mapper
 * @codeCoverageIgnore
 */

namespace PHPExif\Mapper;

/**
 * PHP Exif Mapper
 *
 * Defines the interface for data mappers
 *
 * @category    PHPExif
 * @package     Mapper
 */
abstract class MapperAbstract implements MapperInterface
{
    protected bool $numeric = true;

    /**
     * Mutator method for the numeric property
     *
     * @param bool $numeric
     * @return \PHPExif\Mapper\Exiftool
     */
    public function setNumeric(bool $numeric) : MapperInterface
    {
        $this->numeric = (bool) $numeric;

        return $this;
    }
}
