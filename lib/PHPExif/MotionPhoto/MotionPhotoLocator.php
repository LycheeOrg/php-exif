<?php

namespace PHPExif\MotionPhoto;

use function Safe\fclose;
use function Safe\filesize;
use function Safe\fopen;

/**
 * Finds the video embedded in a Google Motion Photo JPEG.
 *
 * In order:
 *  1. the `Item:Length` of the container item with semantic `MotionPhoto`
 *     (Motion Photo 1.0, Android 11+),
 *  2. the legacy `GCamera:MicroVideoOffset` (Android ≤ 10),
 *  3. if the file declares `GCamera:MotionPhoto="1"` but neither length
 *     points at a video, the first MP4 `ftyp` box after the end of the
 *     primary image.
 *
 * A declared length is only trusted if an `ftyp` box starts where it says.
 * The XMP is read here rather than mapped from the adapters: exiftool
 * flattens `Container:Directory` into parallel lists that no longer line up
 * when an item has no `Item:Length` (the primary item of Pixel files), and
 * the Native and ImageMagick adapters do not expose it at all.
 */
final class MotionPhotoLocator
{
    /**
     * @param string $path path of the file
     *
     * @return int length of the video in bytes, counted from the end of the
     *             file; `0` if there is none or the file cannot be read
     */
    public static function locate(string $path): int
    {
        if (!is_file($path) || !is_readable($path)) {
            return 0;
        }

        $stream = fopen($path, 'rb');
        try {
            return self::locateInStream($stream, filesize($path));
        } catch (\Throwable) {
            return 0;
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param resource $stream
     */
    private static function locateInStream($stream, int $fileSize): int
    {
        $header = JpegXmpReader::read($stream, $fileSize);
        if ($header === null) {
            return 0;
        }

        $xmp = MotionPhotoXmp::fromXml($header->xmp ?? '');
        $declared = self::firstValidLength($stream, $fileSize, $header, [$xmp->itemLength, $xmp->legacyOffset]);
        if ($declared !== 0 || !$xmp->isMotionPhoto) {
            return $declared;
        }

        // Scan only after the primary image, so compressed image data that
        // happens to look like an MP4 header cannot be taken for the video.
        $imageEnd = JpegImageEnd::find($stream, $header->imageDataOffset, $fileSize);
        $videoStart = $imageEnd === null ? null : FtypBox::scan($stream, $imageEnd, $fileSize);

        return $videoStart === null ? 0 : $fileSize - $videoStart;
    }

    /**
     * @param resource $stream
     * @param int[]    $lengths candidate video lengths, `0` meaning "not declared"
     */
    private static function firstValidLength($stream, int $fileSize, JpegHeader $header, array $lengths): int
    {
        foreach ($lengths as $length) {
            $isAfterHeader = $length > 0 && $length <= $fileSize - $header->imageDataOffset;
            if ($isAfterHeader && FtypBox::isAt($stream, $fileSize - $length)) {
                return $length;
            }
        }

        return 0;
    }
}
