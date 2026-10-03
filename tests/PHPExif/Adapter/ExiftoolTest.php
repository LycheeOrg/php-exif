<?php

use PHPExif\Adapter\Exiftool;
use PHPUnit\Framework\Attributes\Group;

class ExiftoolTest extends \PHPUnit\Framework\TestCase
{
    protected Exiftool $adapter;

    public function setUp(): void
    {
        $this->adapter = new Exiftool();
    }

    #[Group('exiftool')]
    public function testGetToolPathFromProperty()
    {
        $reflProperty = new \ReflectionProperty(Exiftool::class, 'toolPath');
        $expected = '/foo/bar/baz';
        $reflProperty->setValue($this->adapter, $expected);

        $this->assertEquals($expected, $this->adapter->getToolPath());
    }

    #[Group('exiftool')]
    public function testSetToolPathInProperty()
    {
        $reflProperty = new \ReflectionProperty(Exiftool::class, 'toolPath');

        $expected = '/tmp';
        $this->adapter->setToolPath($expected);

        $this->assertEquals($expected, $reflProperty->getValue($this->adapter));
    }

    #[Group('exiftool')]
    public function testSetToolPathThrowsException()
    {
        $this->expectException('InvalidArgumentException');
        $this->adapter->setToolPath('/foo/bar');
    }


    #[Group('exiftool')]
    public function testGetToolPathLazyLoadsPath()
    {
        $this->assertIsString($this->adapter->getToolPath());
    }

    #[Group('exiftool')]
    public function testSetNumericInProperty()
    {
        $reflProperty = new \ReflectionProperty(Exiftool::class, 'numeric');

        $expected = true;
        $this->adapter->setNumeric($expected);

        $this->assertEquals($expected, $reflProperty->getValue($this->adapter));
    }

    /**
     * @see URI http://www.sno.phy.queensu.ca/~phil/exiftool/faq.html#Q10
     */
    #[Group('exiftool')]
    public function testSetEncodingInProperty()
    {
        $reflProperty = new \ReflectionProperty(Exiftool::class, 'encoding');

        $expected = array('iptc' => 'cp1250');
        $input = array('iptc' => 'cp1250', 'exif' => 'utf8', 'foo' => 'bar');
        $this->adapter->setEncoding($input);

        $this->assertEquals($expected, $reflProperty->getValue($this->adapter));
    }

    #[Group('exiftool')]
    public function testGetExifFromFile()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/morning_glory_pool_500.jpg';
        $this->adapter->setOptions(array('encoding' => array('iptc' => 'cp1252')));
        $result = $this->adapter->getExifFromFile($file);
        $this->assertInstanceOf('\PHPExif\Exif', $result);
        $this->assertIsArray($result->getRawData());
        $this->assertNotEmpty($result->getRawData());
    }

    #[Group('exiftool')]
    public function testGetExifFromFileWithUtf8()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/utf8.jpg';
        $this->adapter->setOptions(array('encoding' => array('iptc' => 'utf8')));
        $result = $this->adapter->getExifFromFile($file);
        $this->assertInstanceOf('\PHPExif\Exif', $result);
        $this->assertIsArray($result->getRawData());
        $this->assertNotEmpty($result->getRawData());
    }

    #[Group('exiftool')]
    public function testGetExifFromFileWithAvif()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/fox.profile0.10bpc.yuv420.avif';
        $this->adapter->setOptions(array('encoding' => array('utf8')));
        $result = $this->adapter->getExifFromFile($file);
        $this->assertInstanceOf('\PHPExif\Exif', $result);
        $this->assertIsArray($result->getRawData());
        $this->assertNotEmpty($result->getRawData());
    }

    #[Group('exiftool')]
    public function testGetCliOutput()
    {
        $reflMethod = new \ReflectionMethod(Exiftool::class, 'getCliOutput');

        $result = $reflMethod->invoke(
            $this->adapter,
            sprintf(
                '%1$s',
                'pwd'
            )
        );

        $this->assertIsString($result);
    }

    /**
     * Reads the XMP-GPano tags of a photo sphere. ImageMagickTest asserts the
     * same values, so both adapters return identical getter values.
     */
    #[Group('exiftool')]
    public function testGetPanoramaDataFromFile()
    {
        if ($this->adapter->getToolPath() === '') {
            $this->markTestSkipped('exiftool is not available.');
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
