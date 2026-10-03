<?php

use PHPExif\Adapter\Native;
use PHPUnit\Framework\Attributes\Group;

class NativeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Native
     */
    protected Native $adapter;

    public function setUp(): void
    {
        $this->adapter = new Native();
    }

    #[Group('native')]
    public function testSetIncludeThumbnailInProperty()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'includeThumbnail');

        $this->assertEquals(Native::NO_THUMBNAIL, $reflProperty->getValue($this->adapter));

        $this->adapter->setIncludeThumbnail(Native::INCLUDE_THUMBNAIL);

        $this->assertEquals(Native::INCLUDE_THUMBNAIL, $reflProperty->getValue($this->adapter));
    }

    #[Group('native')]
    public function testGetIncludeThumbnailFromProperty()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'includeThumbnail');
        $reflProperty->setValue($this->adapter, Native::INCLUDE_THUMBNAIL);

        $this->assertEquals(Native::INCLUDE_THUMBNAIL, $this->adapter->getIncludeThumbnail());
    }

    #[Group('native')]
    public function testGetIncludeThumbnailHasDefaultValue()
    {
        $this->assertEquals(Native::NO_THUMBNAIL, $this->adapter->getIncludeThumbnail());
    }

    #[Group('native')]
    public function testGetRequiredSections()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'requiredSections');

        $this->assertEquals($reflProperty->getValue($this->adapter), $this->adapter->getRequiredSections());
    }

    #[Group('native')]
    public function testSetRequiredSections()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'requiredSections');

        $testData = array('foo', 'bar', 'baz');

        $returnValue = $this->adapter->setRequiredSections($testData);

        $this->assertEquals($testData, $reflProperty->getValue($this->adapter));
        $this->assertEquals($this->adapter, $returnValue);
    }

    #[Group('native')]
    public function testAddRequiredSection()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'requiredSections');

        $testData = array('foo', 'bar', 'baz');
        $this->adapter->setRequiredSections($testData);

        $returnValue = $this->adapter->addRequiredSection('test');
        array_push($testData, 'test');

        $this->assertEquals($testData, $reflProperty->getValue($this->adapter));
        $this->assertEquals($this->adapter, $returnValue);
    }

    #[Group('native')]
    public function testGetExifFromFileNoData()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/empty.jpg';
        $result = $this->adapter->getExifFromFile($file);
        $expected = array('FileSize' => 17,
                          'FileName' => 'empty.jpg',
                          'MimeType' => 'text/plain');
        $this->assertEquals($expected, $result->getRawData());
    }

    #[Group('native')]
    public function testGetExifFromFileHasData()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/morning_glory_pool_500.jpg';
        $result = $this->adapter->getExifFromFile($file);
        $this->assertInstanceOf('\PHPExif\Exif', $result);
        $this->assertIsArray($result->getRawData());
        $this->assertNotEmpty($result->getRawData());
    }

    #[Group('native')]
    public function testGetIptcData()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/morning_glory_pool_500.jpg';
        $result = $this->adapter->getIptcData($file);
        $expected = array(
            'title' => 'Morning Glory Pool',
            'keywords'  => array(
                '18-200', 'D90', 'USA', 'Wyoming', 'Yellowstone'
            ),
        );

        $this->assertEquals($expected, $result);
    }

    #[Group('native')]
    public function testGetEmptyIptcData()
    {
        $file = PHPEXIF_TEST_ROOT . '/files/empty_iptc.jpg';
        $result = $this->adapter->getIptcData($file);

        $this->assertEquals([], $result);
    }

    #[Group('native')]
    public function testSetSectionsAsArrayInProperty()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'sectionsAsArrays');
        $expected = Native::SECTIONS_AS_ARRAYS;
        $this->adapter->setSectionsAsArrays($expected);
        $actual = $reflProperty->getValue($this->adapter);
        $this->assertEquals($expected, $actual);
    }

    #[Group('native')]
    public function testSetSectionsAsArrayConvertsToBoolean()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'sectionsAsArrays');
        $expected = Native::SECTIONS_AS_ARRAYS;
        $this->adapter->setSectionsAsArrays('Foo');
        $actual = $reflProperty->getValue($this->adapter);
        $this->assertEquals($expected, $actual);
    }

    #[Group('native')]
    public function testGetSectionsAsArrayFromProperty()
    {
        $reflProperty = new \ReflectionProperty(Native::class, 'sectionsAsArrays');
        $reflProperty->setValue($this->adapter, Native::SECTIONS_AS_ARRAYS);

        $this->assertEquals(Native::SECTIONS_AS_ARRAYS, $this->adapter->getSectionsAsArrays());
    }

    /**
     * gpano.jpg carries ExposureCompensation -2/3 EV and manual white balance.
     * The Native, Exiftool and ImageMagick adapter tests assert the same values.
     */
    #[Group('native')]
    public function testGetExposureBiasAndWhiteBalanceFromFile()
    {
        if (!extension_loaded('exif')) {
            $this->markTestSkipped('The exif extension is not available.');
        }
        $result = $this->adapter->getExifFromFile(PHPEXIF_TEST_ROOT . '/files/gpano.jpg');

        $this->assertSame(-0.67, $result->getExposureBias());
        $this->assertSame(1, $result->getWhiteBalance());
    }

    /**
     * The colour temperature is only read by the exiftool adapter,
     * even though gpano.jpg carries XMP-crs:ColorTemperature 5500.
     */
    #[Group('native')]
    public function testGetWhiteBalanceTemperatureIsNotAvailable()
    {
        if (!extension_loaded('exif')) {
            $this->markTestSkipped('The exif extension is not available.');
        }
        $result = $this->adapter->getExifFromFile(PHPEXIF_TEST_ROOT . '/files/gpano.jpg');

        $this->assertFalse($result->getWhiteBalanceTemperature());
    }
}
