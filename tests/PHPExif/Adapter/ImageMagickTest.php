<?php

use PHPExif\Adapter\ImageMagick;
use PHPExif\Exif;
use PHPUnit\Framework\Attributes\Group;

class ImageMagickTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImageMagick
     */
    protected ImageMagick $adapter;

    public function setUp(): void
    {
        $this->adapter = new ImageMagick();
    }

    #[Group('ImageMagick')]
    public function testGetExifFromFile()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/morning_glory_pool_500.jpg';
        $result = $this->adapter->getExifFromFile($file);
        $this->assertInstanceOf(Exif::class, $result);
        $this->assertIsArray($result->getRawData());
        $this->assertNotEmpty($result->getRawData());
    }

    #[Group('ImageMagick')]
    public function testGetEmptyIptcData()
    {
        $result = $this->adapter->getIptcData("");

        $this->assertEquals([], $result);
    }

    /**
     * Reads the XMP-GPano tags of a photo sphere. ExiftoolTest asserts the
     * same values, so both adapters return identical getter values.
     */
    #[Group('ImageMagick')]
    public function testGetPanoramaDataFromFile()
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('The imagick extension is not available.');
        }
        $file = PHPEXIF_TEST_ROOT . '/files/gpano.jpg';
        $result = $this->adapter->getExifFromFile($file);

        $this->assertSame('equirectangular', $result->getProjectionType());
        $this->assertSame(true, $result->getUsePanoramaViewer());
        $this->assertSame(8000, $result->getFullPanoWidthPixels());
        $this->assertSame(4000, $result->getFullPanoHeightPixels());
        $this->assertSame(1000, $result->getCroppedAreaLeftPixels());
        $this->assertSame(1000, $result->getCroppedAreaTopPixels());
        $this->assertSame(6000, $result->getCroppedAreaImageWidthPixels());
        $this->assertSame(2000, $result->getCroppedAreaImageHeightPixels());
    }
}
