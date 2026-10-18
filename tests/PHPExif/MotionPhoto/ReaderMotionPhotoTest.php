<?php

use PHPExif\Adapter\Exiftool;
use PHPExif\Adapter\ImageMagick;
use PHPExif\Adapter\Native;
use PHPExif\Contracts\AdapterInterface;
use PHPExif\Exif;
use PHPExif\Reader\Reader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * The reader reports the video of a Google Motion Photo as
 * `MicroVideoOffset` (its length in bytes from the end of the file) for
 * every adapter, in the legacy and in the Motion Photo 1.0 format.
 */
class ReaderMotionPhotoTest extends TestCase
{
    /**
     * Length of the video appended to both sample files.
     */
    private const VIDEO_LENGTH = '10705';

    /**
     * @return array<string,array{AdapterInterface}>
     */
    public static function imageReaders(): array
    {
        return [
            'native' => [new Native()],
            'imagick' => [new ImageMagick()],
            'exiftool' => [new Exiftool()],
        ];
    }

    #[Group('motionphoto')]
    #[DataProvider('imageReaders')]
    public function testMotionPhotoV1(AdapterInterface $adapter): void
    {
        $exif = (new Reader($adapter))->read(PHPEXIF_TEST_ROOT . '/files/motion_photo_v1.jpg');

        $this->assertSame(self::VIDEO_LENGTH, $exif->getMicroVideoOffset());
    }

    #[Group('motionphoto')]
    #[DataProvider('imageReaders')]
    public function testUltraHdrMotionPhotoSkipsTheGainMap(AdapterInterface $adapter): void
    {
        $exif = (new Reader($adapter))->read(PHPEXIF_TEST_ROOT . '/files/motion_photo_ultrahdr.jpg');

        $this->assertSame(self::VIDEO_LENGTH, $exif->getMicroVideoOffset());
    }

    #[Group('motionphoto')]
    #[DataProvider('imageReaders')]
    public function testPlainPhotoHasNoVideo(AdapterInterface $adapter): void
    {
        $exif = (new Reader($adapter))->read(PHPEXIF_TEST_ROOT . '/files/mongolia.jpeg');

        $this->assertFalse($exif->getMicroVideoOffset());
    }

    #[Group('motionphoto')]
    public function testAdapterValueIsKept(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('getExifFromFile')->willReturn((new Exif())->setMicroVideoOffset('123'));

        $exif = (new Reader($adapter))->read(PHPEXIF_TEST_ROOT . '/files/motion_photo_v1.jpg');

        $this->assertSame('123', $exif->getMicroVideoOffset());
    }

    #[Group('motionphoto')]
    public function testVideosAreLeftAlone(): void
    {
        $adapter = $this->createStub(AdapterInterface::class);
        $adapter->method('getExifFromFile')->willReturn(new Exif());

        $exif = (new Reader($adapter))->read(PHPEXIF_TEST_ROOT . '/files/IMG_3824.MOV');

        $this->assertFalse($exif->getMicroVideoOffset());
    }
}
