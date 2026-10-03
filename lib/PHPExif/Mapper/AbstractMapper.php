<?php

namespace PHPExif\Mapper;

use PHPExif\Contracts\MapperInterface;

/**
 * PHP Exif Mapper Abstract
 *
 * Implements common functionality for data mappers
 *
 * @category    PHPExif
 * @package     Mapper
 */
abstract class AbstractMapper implements MapperInterface
{
    public const ROUNDING_PRECISION = 12;

    /**
     * Trim whitespaces recursively
     *
     * @param mixed $data
     * @return mixed
     */
    public function trim(mixed $data): mixed
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

    /**
     * Normalize a boolean XMP value: true/false (any case), 1/0 or a PHP bool
     *
     * @param mixed $value
     * @return bool|null null if the value is not a recognised boolean
     */
    protected function normalizeBool(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (!is_int($value) && !is_string($value)) {
            return null;
        }

        return match (strtolower(trim((string) $value))) {
            'true', '1' => true,
            'false', '0' => false,
            default => null,
        };
    }

    /**
     * Normalize an integer value
     *
     * @param mixed $value
     * @return int|null null if the value is not numeric
     */
    protected function normalizeInt(mixed $value): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
