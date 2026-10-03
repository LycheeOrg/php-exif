<?php

use PHPExif\Exif;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

class ExifTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Exif
     */
    protected $exif;

    /**
     * Setup function before the tests
     */
    public function setUp(): void
    {
        $this->exif = new Exif();
    }

    #[Group('exif')]
    public function testConstructorCallsSetData()
    {
        $input = [];

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder(Exif::class)
            ->disableOriginalConstructor()
            ->getMock();

        // set expectations for constructor calls
        $mock->expects($this->once())
            ->method('setData')
            ->with(
                $this->equalTo($input)
            );

        // now call the constructor
        $reflectedClass = new ReflectionClass(Exif::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, $input);
    }

    #[Group('exif')]
    public function testGetRawData()
    {
        $reflProperty = new \ReflectionProperty(Exif::class, 'rawData');

        $this->assertEquals($reflProperty->getValue($this->exif), $this->exif->getRawData());
    }

    #[Group('exif')]
    public function testSetRawData()
    {
        $testData = array('foo', 'bar', 'baz');
        $reflProperty = new \ReflectionProperty(Exif::class, 'rawData');

        $result = $this->exif->setRawData($testData);

        $this->assertEquals($testData, $reflProperty->getValue($this->exif));
        $this->assertEquals($this->exif, $result);
    }

    #[Group('exif')]
    public function testGetData()
    {
        $reflProperty = new \ReflectionProperty(Exif::class, 'data');

        $this->assertEquals($reflProperty->getValue($this->exif), $this->exif->getData());
    }

    #[Group('exif')]
    public function testSetData()
    {
        $testData = array('foo', 'bar', 'baz');
        $reflProperty = new \ReflectionProperty(Exif::class, 'data');

        $result = $this->exif->setData($testData);

        $this->assertEquals($testData, $reflProperty->getValue($this->exif));
        $this->assertEquals($this->exif, $result);
    }

    /**
     *
     * @param string $accessor
     */
    #[DataProvider('providerUndefinedPropertiesReturnFalse')]
    public function testUndefinedPropertiesReturnFalse($accessor)
    {
        $expected = false;
        $this->assertEquals($expected, $this->exif->$accessor());
    }

    /**
     * Data provider for testUndefinedPropertiesReturnFalse
     *
     * @return array
     */
    public static function providerUndefinedPropertiesReturnFalse()
    {
        return array(
            array('getAperture'),
            array('getIso'),
            array('getExposure'),
            array('getExposureMilliseconds'),
            array('getFocusDistance'),
            array('getWidth'),
            array('getHeight'),
            array('getTitle'),
            array('getCaption'),
            array('getCopyright'),
            array('getKeywords'),
            array('getCamera'),
            array('getHorizontalResolution'),
            array('getVerticalResolution'),
            array('getSoftware'),
            array('getFocalLength'),
            array('getCreationDate'),
            array('getAuthor'),
            array('getHeadline'),
            array('getCredit'),
            array('getSource'),
            array('getJobtitle'),
            array('getMimeType'),
            array('getFileSize'),
            array('getFileName'),
            array('getHeadline'),
            array('getColorSpace'),
            array('getOrientation'),
            array('getGPS'),
            array('getDescription'),
            array('getMake'),
            array('getAltitude'),
            array('getLatitude'),
            array('getLongitude'),
            array('getImgDirection'),
            array('getLens'),
            array('getContentIdentifier'),
            array('getFramerate'),
            array('getDuration'),
            array('getMicroVideoOffset'),
            array('getCity'),
            array('getSublocation'),
            array('getState'),
            array('getCountry'),
            array('getProjectionType'),
            array('getFullPanoWidthPixels'),
            array('getFullPanoHeightPixels'),
            array('getCroppedAreaLeftPixels'),
            array('getCroppedAreaTopPixels'),
            array('getCroppedAreaImageWidthPixels'),
            array('getCroppedAreaImageHeightPixels'),
        );
    }

    /**
     * UsePanoramaViewer distinguishes "absent" (null) from an explicit false
     */
    #[Group('exif')]
    public function testUndefinedUsePanoramaViewerReturnsNull()
    {
        $this->assertNull($this->exif->getUsePanoramaViewer());
    }

    #[Group('exif')]
    public function testGetAperture()
    {
        $expected = 'f/8.0';
        $data[Exif::APERTURE] = $expected;
        $this->exif->setData($data);

        $this->assertEquals($expected, $this->exif->getAperture());
    }

    #[Group('exif')]
    public function testGetIso()
    {
        $expected = 200;
        $data[Exif::ISO] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getIso());
    }

    #[Group('exif')]
    public function testGetExposure()
    {
        $expected = '1/320';
        $data[Exif::EXPOSURE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getExposure());
    }

    #[Group('exif')]
    public function testGetExposureMilliseconds()
    {
        $rawData = array(
            array(1 / 300, '1/300'),
            array(0.0025, 0.0025),
        );

        foreach ($rawData as $data) {
            $expected = reset($data);
            $value = end($data);

            $data[Exif::EXPOSURE] = $value;
            $this->exif->setData($data);
            $this->assertEquals($expected, $this->exif->getExposureMilliseconds());
        }
    }

    #[Group('exif')]
    public function testGetFocusDistance()
    {
        $expected = '7.94m';
        $data[Exif::FOCAL_DISTANCE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getFocusDistance());
    }

    #[Group('exif')]
    public function testGetWidth()
    {
        $expected = 500;
        $data[Exif::WIDTH] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getWidth());
    }

    #[Group('exif')]
    public function testGetHeight()
    {
        $expected = 332;
        $data[Exif::HEIGHT] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getHeight());
    }

    #[Group('exif')]
    public function testGetTitle()
    {
        $expected = 'Morning Glory Pool';
        $data[Exif::TITLE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getTitle());
    }

    #[Group('exif')]
    public function testGetCaption()
    {
        $expected = 'Foo Bar Baz';
        $data[Exif::CAPTION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getCaption());
    }

    #[Group('exif')]
    public function testGetCopyright()
    {
        $expected = 'Miljar';
        $data[Exif::COPYRIGHT] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getCopyright());
    }

    #[Group('exif')]
    public function testGetKeywords()
    {
        $expected = array('18-200', 'D90', 'USA', 'Wyoming', 'Yellowstone');
        $data[Exif::KEYWORDS] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getKeywords());
    }

    #[Group('exif')]
    public function testGetCamera()
    {
        $expected = 'NIKON D90';
        $data[Exif::CAMERA] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getCamera());
    }

    #[Group('exif')]
    public function testGetHorizontalResolution()
    {
        $expected = 240;
        $data[Exif::HORIZONTAL_RESOLUTION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getHorizontalResolution());
    }

    #[Group('exif')]
    public function testGetVerticalResolution()
    {
        $expected = 240;
        $data[Exif::VERTICAL_RESOLUTION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getVerticalResolution());
    }

    #[Group('exif')]
    public function testGetSoftware()
    {
        $expected = 'Adobe Photoshop Lightroom';
        $data[Exif::SOFTWARE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getSoftware());
    }

    #[Group('exif')]
    public function testGetFocalLength()
    {
        $expected = 18;
        $data[Exif::FOCAL_LENGTH] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getFocalLength());
    }

    #[Group('exif')]
    public function testGetCreationDate()
    {
        $expected = '2011-06-07 20:01:50';
        $input = \DateTime::createFromFormat('Y-m-d H:i:s', $expected);
        $data[Exif::CREATION_DATE] = $input;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getCreationDate()->format('Y-m-d H:i:s'));
    }

    #[Group('exif')]
    public function testGetAuthor()
    {
        $expected = 'John Smith';
        $data[Exif::AUTHOR] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getAuthor());
    }

    #[Group('exif')]
    public function testGetHeadline()
    {
        $expected = 'Foobar Baz';
        $data[Exif::HEADLINE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getHeadline());
    }

    #[Group('exif')]
    public function testGetCredit()
    {
        $expected = 'john.smith@example.com';
        $data[Exif::CREDIT] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getCredit());
    }

    #[Group('exif')]
    public function testGetSource()
    {
        $expected = 'FBB NEWS';
        $data[Exif::SOURCE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getSource());
    }

    #[Group('exif')]
    public function testGetJobtitle()
    {
        $expected = 'Yellowstone\'s geysers and pools';
        $data[Exif::JOB_TITLE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getJobtitle());
    }

    #[Group('exif')]
    public function testGetColorSpace()
    {
        $expected = 'RGB';
        $data[Exif::COLORSPACE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getColorSpace());
    }

    #[Group('exif')]
    public function testGetMimeType()
    {
        $expected = 'image/jpeg';
        $data[Exif::MIMETYPE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getMimeType());
    }

    #[Group('exif')]
    public function testGetFileSize()
    {
        $expected = '27852365';
        $data[Exif::FILESIZE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getFileSize());
    }

    #[Group('exif')]
    public function testGetFileName()
    {
        $expected = '27852365.jpg';
        $data[Exif::FILENAME] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getFileName());
    }

    #[Group('exif')]
    public function testGetOrientation()
    {
        $expected = 1;
        $data[Exif::ORIENTATION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getOrientation());
    }

    #[Group('exif')]
    public function testGetGPS()
    {
        $expected = '40.333452380556,-20.167314813889';
        $data[Exif::GPS] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getGPS());
    }

    #[Group('exif')]
    public function testGetDescription()
    {
        $expected = 'Lorem ipsum';
        $data[Exif::DESCRIPTION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getDescription());
    }

    #[Group('exif')]
    public function testGetMake()
    {
        $expected = 'Make';
        $data[Exif::MAKE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getMake());
    }

    #[Group('exif')]
    public function testGetAltitude()
    {
        $expected = '8848';
        $data[Exif::ALTITUDE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getAltitude());
    }

    #[Group('exif')]
    public function testGetLatitude()
    {
        $expected = '40.333452380556';
        $data[Exif::LATITUDE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getLatitude());
    }

    #[Group('exif')]
    public function testGetLongitude()
    {
        $expected = '-20.167314813889';
        $data[Exif::LONGITUDE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getLongitude());
    }

    #[Group('exif')]
    public function testGetImgDirection()
    {
        $expected = '180';
        $data[Exif::IMGDIRECTION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getImgDirection());
    }

    #[Group('exif')]
    public function testGetLens()
    {
        $expected = '70 - 200mm';
        $data[Exif::LENS] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getLens());
    }

    #[Group('exif')]
    public function testGetContentIdentifier()
    {
        $expected = 'C09DCB26-D321-4254-9F68-2E2E7FA16155';
        $data[Exif::CONTENTIDENTIFIER] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getContentIdentifier());
    }

    #[Group('exif')]
    public function testGetFramerate()
    {
        $expected = '24';
        $data[Exif::FRAMERATE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getFramerate());
    }

    #[Group('exif')]
    public function testGetDuration()
    {
        $expected = '1s';
        $data[Exif::DURATION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getDuration());
    }

    #[Group('exif')]
    public function testGetMicroVideoOffset()
    {
        $expected = '3062730';
        $data[Exif::MICROVIDEOOFFSET] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getMicroVideoOffset());
    }

    #[Group('exif')]
    public function testGetCity()
    {
        $expected = 'New York';
        $data[Exif::CITY] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getCity());
    }

    #[Group('exif')]
    public function testGetSublocation()
    {
        $expected = 'sublocation';
        $data[Exif::SUBLOCATION] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getSublocation());
    }

    #[Group('exif')]
    public function testGetState()
    {
        $expected = 'New York';
        $data[Exif::STATE] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getState());
    }

    #[Group('exif')]
    public function testGetCountry()
    {
        $expected = 'USA';
        $data[Exif::COUNTRY] = $expected;
        $this->exif->setData($data);
        $this->assertEquals($expected, $this->exif->getCountry());
    }

    #[Group('exif')]
    public function testGetProjectionType()
    {
        $expected = 'equirectangular';
        $data[Exif::PROJECTIONTYPE] = $expected;
        $this->exif->setData($data);
        $this->assertSame($expected, $this->exif->getProjectionType());
    }

    #[Group('exif')]
    public function testGetUsePanoramaViewer()
    {
        foreach (array(true, false) as $expected) {
            $data[Exif::USEPANORAMAVIEWER] = $expected;
            $this->exif->setData($data);
            $this->assertSame($expected, $this->exif->getUsePanoramaViewer());
        }
    }

    /**
     * Data provider for the integer GPano crop fields
     *
     * @return array
     */
    public static function providerPanoCropFields()
    {
        return array(
            array(Exif::FULLPANOWIDTHPIXELS, 'FullPanoWidthPixels', 8000),
            array(Exif::FULLPANOHEIGHTPIXELS, 'FullPanoHeightPixels', 4000),
            array(Exif::CROPPEDAREALEFTPIXELS, 'CroppedAreaLeftPixels', 1000),
            array(Exif::CROPPEDAREATOPPIXELS, 'CroppedAreaTopPixels', 0),
            array(Exif::CROPPEDAREAIMAGEWIDTHPIXELS, 'CroppedAreaImageWidthPixels', 6000),
            array(Exif::CROPPEDAREAIMAGEHEIGHTPIXELS, 'CroppedAreaImageHeightPixels', 2000),
        );
    }

    #[Group('exif')]
    #[DataProvider('providerPanoCropFields')]
    public function testGetPanoCropFields($constant, $name, $expected)
    {
        $data[$constant] = $expected;
        $this->exif->setData($data);
        $getter = 'get' . $name;
        $this->assertSame($expected, $this->exif->$getter());
    }

    #[Group('exif')]
    #[DataProvider('providerPanoCropFields')]
    public function testSetPanoCropFields($constant, $name, $expected)
    {
        $setter = 'set' . $name;
        $getter = 'get' . $name;
        $result = $this->exif->$setter($expected);

        $this->assertSame($this->exif, $result);
        $this->assertSame($expected, $this->exif->$getter());
        $this->assertSame(array($constant => $expected), $this->exif->getData());
    }

    #[Group('exif')]
    public function testSetProjectionType()
    {
        $result = $this->exif->setProjectionType('equirectangular');

        $this->assertSame($this->exif, $result);
        $this->assertSame('equirectangular', $this->exif->getProjectionType());
        $this->assertSame(array(Exif::PROJECTIONTYPE => 'equirectangular'), $this->exif->getData());
    }

    #[Group('exif')]
    public function testSetUsePanoramaViewer()
    {
        foreach (array(true, false) as $expected) {
            $result = $this->exif->setUsePanoramaViewer($expected);

            $this->assertSame($this->exif, $result);
            $this->assertSame($expected, $this->exif->getUsePanoramaViewer());
            $this->assertSame(array(Exif::USEPANORAMAVIEWER => $expected), $this->exif->getData());
        }
    }

    #[Group('exif')]
    public function testMutatorMethodsSetInProperty()
    {
        $reflClass = new \ReflectionClass(get_class($this->exif));
        $constants = $reflClass->getConstants();

        $reflProp = new \ReflectionProperty(get_class($this->exif), 'data');

        $expected = 'foo';
        foreach ($constants as $name => $value) {
            $setter = 'set' . ucfirst($value);

            switch ($value) {
                case 'altitude':
                case 'imgDirection':
                case 'latitude':
                case 'longitude':
                    $coord = 1.2345;
                    $this->exif->$setter($coord);
                    $propertyValue = $reflProp->getValue($this->exif);
                    $this->assertEquals($coord, $propertyValue[$value]);
                    break;
                case 'creationdate':
                    $now = new \DateTime();
                    $this->exif->$setter($now);
                    $propertyValue = $reflProp->getValue($this->exif);
                    $this->assertSame($now, $propertyValue[$value]);
                    break;
                case 'FileSize':
                    $size = 10123456;
                    $this->exif->$setter($size);
                    $propertyValue = $reflProp->getValue($this->exif);
                    $this->assertEquals($size, $propertyValue[$value]);
                    break;
                case 'gps':
                    $coords = '40.333452380556,-20.167314813889';
                    $setter = 'setGPS';
                    $this->exif->$setter($coords);
                    $propertyValue = $reflProp->getValue($this->exif);
                    $this->assertEquals($coords, $propertyValue[$value]);
                    break;
                case 'FullPanoWidthPixels':
                case 'FullPanoHeightPixels':
                case 'CroppedAreaLeftPixels':
                case 'CroppedAreaTopPixels':
                case 'CroppedAreaImageWidthPixels':
                case 'CroppedAreaImageHeightPixels':
                    $pixels = 1234;
                    $this->exif->$setter($pixels);
                    $propertyValue = $reflProp->getValue($this->exif);
                    $this->assertSame($pixels, $propertyValue[$value]);
                    break;
                case 'UsePanoramaViewer':
                    $this->exif->$setter(false);
                    $propertyValue = $reflProp->getValue($this->exif);
                    $this->assertSame(false, $propertyValue[$value]);
                    break;
                case 'focalDistance':
                    $setter = 'setFocusDistance';
                    // no break
                default:
                    $this->exif->$setter($expected);
                    $propertyValue = $reflProp->getValue($this->exif);
                    $this->assertEquals($expected, $propertyValue[$value]);
                    break;
            }
        }
    }

    /**
     * Test that the values returned by different adapters are equal
     */
    #[Group('consistency')]
    public function testAdapterConsistency()
    {
        $reflClass = new \ReflectionClass(Exif::class);
        $methods = $reflClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $testfiles = array(
            PHPEXIF_TEST_ROOT . '/files/morning_glory_pool_500.jpg',
            PHPEXIF_TEST_ROOT . '/files/dsc_5794.jpg',
            PHPEXIF_TEST_ROOT . '/files/dsc_0003.jpg',
            PHPEXIF_TEST_ROOT . '/files/mongolia.jpeg',
            PHPEXIF_TEST_ROOT . '/files/utf8.jpg',
        );

        $adapter_exiftool = new \PHPExif\Adapter\Exiftool();
        $adapter_imagemagick = new \PHPExif\Adapter\ImageMagick();
        $adapter_native = new \PHPExif\Adapter\Native();

        foreach ($testfiles as $file) {
            $result_exif_exiftool = $adapter_exiftool->getExifFromFile($file);
            $result_exif_imagemagick = $adapter_imagemagick->getExifFromFile($file);
            $result_exif_native = $adapter_native->getExifFromFile($file);

            // find all Getter methods on the results and compare its output
            foreach ($methods as $method) {
                $name = $method->getName();
                if (
                    strpos($name, 'get') !== 0 || $name === 'getRawData' || $name === 'getData' || $name === 'getColorSpace' ||
                    ($name === 'getLens' && $file === PHPEXIF_TEST_ROOT . '/files/dsc_5794.jpg') ||
                    ($file === PHPEXIF_TEST_ROOT . '/files/mongolia.jpeg' && ($name === 'getKeywords' || $name === 'getLens')) ||
                    ($file === PHPEXIF_TEST_ROOT . '/files/utf8.jpg' && ($name === 'getAuthor' || $name === 'getDescription'))
                ) {
                    continue;
                }
                $result_native = $result_exif_native->$name();
                $result_exif = $result_exif_exiftool->$name();
                $result_imagemagick = $result_exif_imagemagick->$name();
                $this->assertEquals(
                    $result_native,
                    $result_exif,
                    'Adapter difference detected native/exiftool in method "' . $name . '" on image "' . basename($file) . '"'
                );

                if (in_array(basename($file), ['dsc_0003.jpg'], true)
                    && in_array($name, ['getGPS', 'getLongitude', 'getLatitude'], true)
                    && in_array($result_native, ['1,1', 1.0], true)
                    && in_array($result_imagemagick, ['1.017917240991,1.075044832787', 1.017917240991, 1.075044832787], true)) {
                    // Skip that test...
                    // Something is going wrong here, no clue what.
                    // Suspect it is on php-imagick side.
                    continue;
                }
                $this->assertEquals(
                    $result_native,
                    $result_imagemagick,
                    'Adapter difference detected native/imagemagick in method "' . $name . '" on image "' . basename($file) . '"'
                );
            }
        }

        // Native adapter does not support PNG files so we can't use it in
        // this case.
        $testfiles = array(
            PHPEXIF_TEST_ROOT . '/files/1945c1.png'
        );

        foreach ($testfiles as $file) {
            $result_exif_exiftool = $adapter_exiftool->getExifFromFile($file);
            $result_exif_imagemagick = $adapter_imagemagick->getExifFromFile($file);

            // find all Getter methods on the results and compare its output
            foreach ($methods as $method) {
                $name = $method->getName();
                if (strpos($name, 'get') !== 0 || $name === 'getRawData' || $name === 'getData' || $name === 'getColorSpace') {
                    continue;
                }
                $result_exif = $result_exif_exiftool->$name();
                $result_imagemagick = $result_exif_imagemagick->$name();
                $this->assertEquals(
                    $result_exif,
                    $result_imagemagick,
                    'Adapter difference detected exiftool/imagemagick in method "' . $name . '" on image "' . basename($file) . '"'
                );
            }
        }

        // Test for .avif type image for Native adapter
        $testfiles = array(
            PHPEXIF_TEST_ROOT . '/files/fox.profile0.10bpc.yuv420.avif',
        );

        foreach ($testfiles as $file) {
            $file_name = basename($file);
            $adapter_native = new \PHPExif\Adapter\Native();
            $result_exif_native = $adapter_native->getExifFromFile($file);

            $this->assertEquals($file_name, $result_exif_native->getFileName());
            $this->assertEquals('image/avif', $result_exif_native->getMimeType());
            $this->assertIsNumeric($result_exif_native->getWidth());
        }
    }
}
