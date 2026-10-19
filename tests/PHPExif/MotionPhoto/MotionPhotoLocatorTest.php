<?php

use PHPExif\MotionPhoto\MotionPhotoLocator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Covers locating the video of a motion photo: declared lengths checked
 * against an MP4 `ftyp` box, and the scan after the primary image.
 */
class MotionPhotoLocatorTest extends TestCase
{
    private const CHUNK_SIZE = 65536;

    /** @var string[] */
    private array $files = [];

    protected function tearDown(): void
    {
        array_map(fn (string $path) => @unlink($path), $this->files);
        parent::tearDown();
    }

    #[Group('motionphoto')]
    public function testDeclaredItemLength(): void
    {
        $video = self::mp4(5000);
        $file = $this->file(self::jpeg(self::motionPhoto(strlen($video))) . $video);

        self::assertSame(5000, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testUltraHdrLayoutSkipsTheGainMap(): void
    {
        $video = self::mp4(5000);
        $gainMap = self::jpeg('');
        $file = $this->file(self::jpeg(self::motionPhoto(strlen($video))) . $gainMap . $video);

        self::assertSame(5000, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testDeclaredLegacyOffset(): void
    {
        $video = self::mp4(3000);
        $file = $this->file(self::jpeg('GCamera:MicroVideo="1" GCamera:MicroVideoOffset="3000"') . $video);

        self::assertSame(3000, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testWrongDeclaredLengthFallsBackToTheScan(): void
    {
        $video = self::mp4(5000);
        $file = $this->file(self::jpeg(self::motionPhoto(5007)) . $video);

        self::assertSame(5000, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testWrongLegacyOffsetIsNotScanned(): void
    {
        $video = self::mp4(3000);
        $file = $this->file(self::jpeg('GCamera:MicroVideo="1" GCamera:MicroVideoOffset="3333"') . $video);

        self::assertSame(0, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testMissingVideoItemIsScanned(): void
    {
        $video = self::mp4(4000);
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . $video);

        self::assertSame(4000, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testScanMiss(): void
    {
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . str_repeat("\x00", 4000));

        self::assertSame(0, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testScanRejectsUnknownBrands(): void
    {
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . self::mp4(4000, 'heic'));

        self::assertSame(0, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testScanRejectsImplausibleBoxSizes(): void
    {
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . self::mp4(4000, 'isom', 4096));

        self::assertSame(0, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testScanFindsABoxAcrossAChunkBoundary(): void
    {
        $video = self::mp4(4000);
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"') . str_repeat("\x00", self::CHUNK_SIZE - 6) . $video);

        self::assertSame(4000, MotionPhotoLocator::locate($file));
    }

    /**
     *: compressed image data may contain bytes that look like
     * an MP4 header; the scan starts after the primary image's EOI marker.
     */
    #[Group('motionphoto')]
    public function testScanSkipsTheImageDataOfThePrimaryImage(): void
    {
        $lookAlike = self::mp4(24);
        $video = self::mp4(4000);
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"', 200, $lookAlike . "\x12\x12") . $video);

        self::assertSame(4000, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testScanFindsAnEoiAcrossAChunkBoundary(): void
    {
        $video = self::mp4(4000);
        // SOS at offset 0 of the image data: FF at 65535, D9 at 65536.
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"', self::CHUNK_SIZE + 1) . $video);

        self::assertSame(4000, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testScanNeedsTheEndOfThePrimaryImage(): void
    {
        $truncated = substr(self::jpeg('GCamera:MotionPhoto="1"'), 0, -2);
        $file = $this->file($truncated . self::mp4(4000));

        self::assertSame(0, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testPlainJpegWithTrailingVideoIsNotAMotionPhoto(): void
    {
        $file = $this->file(self::jpeg('') . self::mp4(4000));

        self::assertSame(0, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testLengthOfTheWholeFileIsRejected(): void
    {
        $video = self::mp4(4000);
        $bytes = self::jpeg(self::motionPhoto(999999)) . $video;
        $file = $this->file($bytes);

        self::assertSame(4000, MotionPhotoLocator::locate($file), 'falls back to the scan');
    }

    #[Group('motionphoto')]
    public function testNotAJpeg(): void
    {
        $file = $this->file(self::mp4(4000));

        self::assertSame(0, MotionPhotoLocator::locate($file));
    }

    #[Group('motionphoto')]
    public function testUnreadableFileReturnsZero(): void
    {
        self::assertSame(0, MotionPhotoLocator::locate('/nonexistent/' . uniqid() . '.jpg'));
    }

    #[Group('motionphoto')]
    public function testSyntheticSamples(): void
    {
        self::assertSame(10705, MotionPhotoLocator::locate(PHPEXIF_TEST_ROOT . '/files/motion_photo_v1.jpg'));
        self::assertSame(10705, MotionPhotoLocator::locate(PHPEXIF_TEST_ROOT . '/files/motion_photo_ultrahdr.jpg'));
    }

    #[Group('motionphoto')]
    public function testScanUsesConstantMemory(): void
    {
        $video = self::mp4(4000);
        $file = $this->file(self::jpeg('GCamera:MotionPhoto="1"', 20 * 1024 * 1024) . $video);
        gc_collect_cycles();
        memory_reset_peak_usage();
        $before = memory_get_usage();

        self::assertSame(4000, MotionPhotoLocator::locate($file));
        self::assertLessThan(2 * 1024 * 1024, memory_get_peak_usage() - $before);
    }

    private function file(string $bytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'phpexif-mp-');
        $this->files[] = $path;
        file_put_contents($path, $bytes);

        return $path;
    }

    private static function motionPhoto(int $videoLength): string
    {
        return 'GCamera:MotionPhoto="1">' .
            '<Container:Directory><rdf:Seq>' .
            '<rdf:li rdf:parseType="Resource"><Container:Item Item:Mime="image/jpeg" Item:Semantic="Primary"/></rdf:li>' .
            '<rdf:li rdf:parseType="Resource"><Container:Item Item:Mime="video/mp4" Item:Semantic="MotionPhoto" Item:Length="' . $videoLength . '"/></rdf:li>' .
            '</rdf:Seq></Container:Directory></rdf:Description><rdf:Description';
    }

    /**
     * A JPEG whose XMP carries `$camera` (attributes, optionally closing the
     * description early to add a container directory), and whose image data
     * spans `$imageBytes` bytes from the SOS marker to the end of EOI, ending
     * with `$imageData`.
     */
    private static function jpeg(string $camera, int $imageBytes = 64, string $imageData = ''): string
    {
        $xmp = $camera === '' ? '' : "http://ns.adobe.com/xap/1.0/\x00" .
            '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
            '<rdf:Description xmlns:GCamera="http://ns.google.com/photos/1.0/camera/" xmlns:Container="http://ns.google.com/photos/1.0/container/" xmlns:Item="http://ns.google.com/photos/1.0/container/item/" ' .
            $camera . '/></rdf:RDF></x:xmpmeta>';
        $app1 = $xmp === '' ? '' : "\xFF\xE1" . pack('n', strlen($xmp) + 2) . $xmp;
        $sos = "\xFF\xDA\x00\x08\x01\x01\x00\x00\x3F\x00";

        return "\xFF\xD8" . $app1 . $sos . str_repeat("\x12", $imageBytes - 12 - strlen($imageData)) . $imageData . "\xFF\xD9";
    }

    private static function mp4(int $length, string $brand = 'isom', int $boxSize = 24): string
    {
        $ftyp = pack('N', $boxSize) . 'ftyp' . $brand . "\x00\x00\x02\x00" . 'isommp41';

        return $ftyp . str_repeat("\x00", $length - strlen($ftyp));
    }
}
