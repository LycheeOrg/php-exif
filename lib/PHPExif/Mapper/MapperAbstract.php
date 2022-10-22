<?php
/**
 * PHP Exif Mapper Abstract: Common functionality for data mappers
 *
 * @link        http://github.com/miljar/PHPExif for the canonical source repository
 * @copyright   Copyright (c) 2015 Tom Van Herreweghe <tom@theanalogguy.be>
 * @license     http://github.com/miljar/PHPExif/blob/master/LICENSE MIT License
 * @category    PHPExif
 * @package     Mapper
 */

namespace PHPExif\Mapper;

/**
 * PHP Exif Mapper Abstract
 *
 * Implements common functionality for data mappers
 *
 * @category    PHPExif
 * @package     Mapper
 */
abstract class MapperAbstract implements MapperInterface
{
    /**
     * Trim whitespaces recursively
     *
     * @param mixed $data
     * @return mixed
     */
    public function trim(mixed $data) : mixed
    {
        if (is_array($data)) {
            /** @var mixed $v */
            foreach ($data as $k => $v) {
                $data[$k] = $this->trim($v);
            }
        } elseif (is_string($data)) {
            $data = trim($data);
        }
        return $data;
    }
}
