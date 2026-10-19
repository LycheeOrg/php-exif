<?php

namespace PHPExif\MotionPhoto;

/**
 * Result of {@see JpegXmpReader::read()}.
 */
final readonly class JpegHeader
{
    /**
     * @param string|null $xmp               the first standard XMP packet, without its namespace prefix
     * @param int         $imageDataOffset offset of the start-of-scan marker, i.e. where the header ends
     */
    public function __construct(
        public ?string $xmp,
        public int $imageDataOffset,
    ) {
    }
}
