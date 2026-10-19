<?php

namespace PHPExif\MotionPhoto;

use function Safe\fread;

/**
 * Finds the end-of-image marker of a JPEG, so that what follows it (gain
 * map, motion photo video) can be told apart from the compressed image data.
 *
 * Inside entropy-coded data every `0xFF` byte is followed by `0x00` (byte
 * stuffing) or a restart marker, so the first `FF D9` after the start-of-scan
 * marker is the end of the image.
 */
final class JpegImageEnd
{
    private const EOI = "\xFF\xD9";

    /**
     * @param resource $stream
     * @param int      $from      offset of the start-of-scan marker
     * @param int      $fileSize size of the file in bytes
     *
     * @return int|null offset just after the EOI marker; null if the image never ends
     */
    public static function find($stream, int $from, int $fileSize): ?int
    {
        fseek($stream, $from);
        $offset = $from;
        $carry = '';
        while ($offset < $fileSize) {
            $chunk = fread($stream, FtypBox::CHUNK_SIZE);
            if ($chunk === '') {
                return null;
            }

            // The last byte of the previous chunk is kept, so a marker split
            // across two chunks is still seen whole.
            $buffer = $carry . $chunk;
            $eoiAt = strpos($buffer, self::EOI);
            if ($eoiAt !== false) {
                return $offset - strlen($carry) + $eoiAt + strlen(self::EOI);
            }

            $carry = substr($buffer, -1);
            $offset += strlen($chunk);
        }

        return null;
    }
}
