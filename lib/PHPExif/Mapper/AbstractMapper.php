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

    /**
     * Normalize the exposure bias to EV, rounded to 2 decimals.
     * Accepts a number or a rational string such as "-2/3".
     * Rounding keeps the readers consistent: exiftool -n only
     * prints 10 significant digits, e.g. -0.6666666667.
     *
     * @param mixed $value
     * @return float|null null if the value is not a valid number or rational
     */
    protected function normalizeExposureBias(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }
        if (!is_string($value)) {
            return null;
        }

        $parts = explode('/', $value, 2);
        if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1]) || (float) $parts[1] === 0.0) {
            return null;
        }

        return round((float) $parts[0] / (float) $parts[1], 2);
    }

    /**
     * Normalize the EXIF white balance mode: 0 = auto, 1 = manual.
     * Accepts the numeric code or the exiftool labels "Auto" and "Manual".
     *
     * @param mixed $value
     * @return int|null null if the value is not recognised
     */
    protected function normalizeWhiteBalance(mixed $value): ?int
    {
        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'auto':
                    return 0;
                case 'manual':
                    return 1;
            }
        }

        return $this->normalizeInt($value);
    }
}
