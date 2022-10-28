<?php
/**
 * @codeCoverageIgnore
 */

namespace PHPExif\Adapter;

use PHPExif\Exif;

/**
 * PHP Exif Reader Adapter
 *
 * Defines the interface for reader adapters
 *
 * @category    PHPExif
 * @package     Reader
 */
interface AdapterInterface
{
    /**
     * Reads & parses the EXIF data from given file
     *
     * @param string $file
     * @return \PHPExif\Exif Instance of Exif object with data
     * @throws \RuntimeException If the EXIF data could not be read
     */
    public function getExifFromFile(string $file) : Exif|false;
}
