<?php

use PHPExif\Contracts\MapperInterface;
use PHPExif\Exif;
use PHPExif\Mapper\ImageMagick;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

class ImageMagickMapperTest extends \PHPUnit\Framework\TestCase
{
    protected $mapper;

    public function setUp(): void
    {
        $this->mapper = new ImageMagick();
    }

    #[Group('mapper')]
    public function testClassImplementsCorrectInterface()
    {
        $this->assertInstanceOf(MapperInterface::class, $this->mapper);
    }

    #[Group('mapper')]
    public function testMapRawDataIgnoresFieldIfItDoesntExist()
    {
        $rawData = array('foo' => 'bar');
        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertCount(0, $mapped);
    }

    #[Group('mapper')]
    public function testMapRawDataMapsFieldsCorrectly()
    {
        $reflProp = new \ReflectionProperty(get_class($this->mapper), 'map');
        $map = $reflProp->getValue($this->mapper);

        // ignore custom formatted data stuff:
        unset($map[ImageMagick::APERTURE]);
        unset($map[ImageMagick::EXPOSURETIME]);
        unset($map[ImageMagick::FOCALLENGTH]);
        unset($map[ImageMagick::GPSLATITUDE]);
        unset($map[ImageMagick::GPSLONGITUDE]);
        unset($map[ImageMagick::DATETIMEORIGINAL]);
        unset($map[ImageMagick::ISO]);
        unset($map[ImageMagick::LENS]);
        unset($map[ImageMagick::WIDTH]);
        unset($map[ImageMagick::HEIGHT]);
        unset($map[ImageMagick::IMAGEHEIGHT_PNG]);
        unset($map[ImageMagick::IMAGEWIDTH_PNG]);
        unset($map[ImageMagick::COPYRIGHT_IPTC]);
        unset($map[ImageMagick::PROJECTIONTYPE]);
        unset($map[ImageMagick::EXPOSUREBIAS]);
        unset($map[ImageMagick::WHITEBALANCE]);
        unset($map[ImageMagick::USEPANORAMAVIEWER]);
        unset($map[ImageMagick::FULLPANOWIDTHPIXELS]);
        unset($map[ImageMagick::FULLPANOHEIGHTPIXELS]);
        unset($map[ImageMagick::CROPPEDAREALEFTPIXELS]);
        unset($map[ImageMagick::CROPPEDAREATOPPIXELS]);
        unset($map[ImageMagick::CROPPEDAREAIMAGEWIDTHPIXELS]);
        unset($map[ImageMagick::CROPPEDAREAIMAGEHEIGHTPIXELS]);

        // create raw data
        $keys = array_unique(array_keys($map));
        $values = [];
        $values = array_pad($values, count($keys), 'foo');
        $rawData = array_combine($keys, $values);

        $mapped = $this->mapper->mapRawData($rawData);

        $i = 0;
        foreach ($mapped as $key => $value) {
            $this->assertEquals($map[$keys[$i]], $key);
            $i++;
        }
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsAperture()
    {
        $rawData = array(
            ImageMagick::APERTURE => '54823/32325',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals('f/1.7', reset($mapped));
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsDateTimeOriginal()
    {
        $rawData = array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01 12:11:09',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $result = reset($mapped);
        $this->assertInstanceOf('\\DateTime', $result);
        $this->assertEquals(
            reset($rawData),
            $result->format('Y:m:d H:i:s')
        );
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsCreationDateWithTimeZone()
    {
        $data = array(
          array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01 12:11:09+0200',
          ),
          array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'exif:OffsetTimeOriginal' => '+0200',
          ),
          array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'exif:OffsetTime' => '+0200',
          ),
          array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'exif:OffsetTimeOriginal' => '+0200',
          )
        );

        foreach ($data as $key => $rawData) {
            $mapped = $this->mapper->mapRawData($rawData);

            $result = reset($mapped);
            $this->assertInstanceOf('\\DateTime', $result);
            $this->assertEquals(
                '2015:04:01 12:11:09',
                $result->format('Y:m:d H:i:s')
            );
            $this->assertEquals(
                7200,
                $result->getOffset()
            );
            $this->assertEquals(
                '+02:00',
                $result->getTimezone()->getName()
            );
        }

    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsCreationDateWithTimeZone2()
    {
        $rawData = array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'exif:OffsetTimeOriginal' => '+0200',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $result = reset($mapped);
        $this->assertInstanceOf('\\DateTime', $result);
        $this->assertEquals(
            '2015:04:01 12:11:09',
            $result->format('Y:m:d H:i:s')
        );
        $this->assertEquals(
            7200,
            $result->getOffset()
        );
        $this->assertEquals(
            '+02:00',
            $result->getTimezone()->getName()
        );
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyIgnoresIncorrectDateTimeOriginal()
    {
        $rawData = array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(false, reset($mapped));
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyIgnoresIncorrectTimeZone()
    {
        $rawData = array(
            ImageMagick::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'exif:OffsetTimeOriginal' => '   :  ',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $result = reset($mapped);
        $this->assertInstanceOf('\\DateTime', $result);
        $this->assertEquals(
            '2015:04:01 12:11:09',
            $result->format('Y:m:d H:i:s')
        );
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsExposureTime()
    {
        $rawData = array(
            '1/30'  => 10/300,
            '1/400' => 2/800,
            '1/400' => 1/400,
            '0'     => 0,
        );

        foreach ($rawData as $expected => $value) {
            $mapped = $this->mapper->mapRawData(array(
                ImageMagick::EXPOSURETIME => $value,
            ));

            $this->assertEquals($expected, reset($mapped));
        }
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsFocalLength()
    {
        $rawData = array(
            ImageMagick::FOCALLENGTH => '15 m',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(15, reset($mapped));
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsGPSData()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSLATITUDE  => '40/1, 20/1, 42857/100000',
                'exif:GPSLatitudeRef'                   => 'N',
                ImageMagick::GPSLONGITUDE => '20/1, 10/1, 233333/100000',
                'exif:GPSLongitudeRef'                  => 'W',
            )
        );
        $expected_gps = '40.333452380556,-20.167314813889';
        $expected_lat = '40.333452380556';
        $expected_lon = '-20.167314813889';
        $this->assertCount(3, $result);
        $this->assertEquals($expected_gps, $result['gps']);
        $this->assertEquals($expected_lat, $result['latitude']);
        $this->assertEquals($expected_lon, $result['longitude']);
    }

    #[Group('mapper')]
    public function testMapRawDataIncorrectlyFormatedGPSData()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSLATITUDE  => '40/1 20/1 42857/100000',
                'exif:GPSLatitudeRef'                     => 'N',
                ImageMagick::GPSLONGITUDE => '20/1 10/1 233333/100000',
                'exif:GPSLongitudeRef'                    => 'W',
            )
        );
        $this->assertCount(0, $result);
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsNumericGPSData()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSLATITUDE  => '40.333452381',
                'exif:GPSLatitudeRef'                   => 'North',
                ImageMagick::GPSLONGITUDE => '20.167314814',
                'exif:GPSLongitudeRef'                  => 'West',
            )
        );

        $expected_gps = '40.333452381,-20.167314814';
        $expected_lat = '40.333452381';
        $expected_lon = '-20.167314814';
        $this->assertCount(3, $result);
        $this->assertEquals($expected_gps, $result['gps']);
        $this->assertEquals($expected_lat, $result['latitude']);
        $this->assertEquals($expected_lon, $result['longitude']);
    }

    #[Group('mapper')]
    public function testMapRawDataOnlyLatitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSLATITUDE => '40.333452381',
                'exif:GPSLatitudeRef'                    => 'North',
            )
        );

        $this->assertCount(1, $result);
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyIgnoresEmptyGPSData()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSLATITUDE  => '0/0, 0/0, 0/0',
                'exif:GPSLatitudeRef'                     => '',
                ImageMagick::GPSLONGITUDE => '0/0, 0/0, 0/0',
                'exif:GPSLongitudeRef'                    => '',
            )
        );

        $this->assertEquals(false, reset($result));
    }


    public function testMapRawDataCorrectlyFormatsDifferentDateTimeString()
    {
        $rawData = array(
            ImageMagick::DATETIMEORIGINAL => '2014-12-15 00:12:00'
        );

        $mapped = $this->mapper->mapRawData(
            $rawData
        );

        $result = reset($mapped);
        $this->assertInstanceOf('\DateTime', $result);
        $this->assertEquals(
            reset($rawData),
            $result->format("Y-m-d H:i:s")
        );
    }

    public function testMapRawDataCorrectlyIgnoresInvalidCreateDate()
    {
        $rawData = array(
            ImageMagick::DATETIMEORIGINAL => 'Invalid Date String'
        );

        $result = $this->mapper->mapRawData(
            $rawData
        );

        $this->assertCount(0, $result);
        $this->assertNotEquals(
            reset($rawData),
            $result
        );
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyAltitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSALTITUDE  => '122053/1000',
                'exif:GPSAltitudeRef'                   => '0',
            )
        );
        $expected = 122.053;
        $this->assertEquals($expected, reset($result));
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyNegativeAltitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSALTITUDE  => '122053/1000',
                'exif:GPSAltitudeRef'                   => '1',
            )
        );
        $expected = '-122.053';
        $this->assertEquals($expected, reset($result));
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyIgnoresIncorrectAltitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                ImageMagick::GPSALTITUDE  => '0/0',
                'exif:GPSAltitudeRef'                     => '0',
            )
        );
        $this->assertEquals(false, reset($result));
    }


        #[Group('mapper')]
        public function testMapRawDataCorrectlyIsoFormats()
        {
            $expected = array(
                '80' => array(
                    'exif:PhotographicSensitivity'     => '80',
                ),
                '800' => array(
                    'exif:PhotographicSensitivity'     => '800 0 0',
                ),
                '100' => array(
                    'exif:PhotographicSensitivity'     => '100, 0, 0',
                ),
            );

            foreach ($expected as $key => $value) {
                $result = $this->mapper->mapRawData($value);
                $this->assertEquals($key, reset($result));
            }
        }

        #[Group('mapper')]
        public function testMapRawDataCorrectlyHeightPNG()
        {

            $rawData = array(
                '600'  => array(
                                  ImageMagick::IMAGEHEIGHT_PNG  => '800, 600',
                              ),
            );

            foreach ($rawData as $expected => $value) {
                $mapped = $this->mapper->mapRawData($value);

                $this->assertEquals($expected, $mapped['height']);
            }
        }



      #[Group('mapper')]
      public function testMapRawDataCorrectlyWidthPNG()
      {

          $rawData = array(
              '800'  => array(
                                ImageMagick::IMAGEWIDTH_PNG  => '800, 600',
                            ),
          );

          foreach ($rawData as $expected => $value) {
              $mapped = $this->mapper->mapRawData($value);

              $this->assertEquals($expected, $mapped['width']);
          }
      }

      #[Group('mapper')]
      public function testNormalizeComponentCorrectly()
      {
          $reflMethod = new \ReflectionMethod(ImageMagick::class, 'normalizeComponent');

          $rawData = array(
              '2/800' => 0.0025,
              '1/400' => 0.0025,
              '0/1'   => 0,
              '1/0'   => 0,
              '0'     => 0,
              'A'     => 0,
              'A/1'     => 0,
              '1/A'     => 0,
              'A/A'     => 0,
          );

          foreach ($rawData as $value => $expected) {
              $normalized = $reflMethod->invoke($this->mapper, $value);

              $this->assertEquals($expected, $normalized);
          }
      }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyKeywords()
    {
        $rawData = array(
            ImageMagick::KEYWORDS => 'Keyword_1 Keyword_2',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(
            ['Keyword_1 Keyword_2'],
            reset($mapped)
        );
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyKeywordsAndSubject()
    {
        $rawData = array(
            ImageMagick::KEYWORDS => array('Keyword_1', 'Keyword_2'),
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(
            array('Keyword_1' ,'Keyword_2'),
            reset($mapped)
        );
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsXResolution()
    {
        $rawData = array(
            ImageMagick::XRESOLUTION => '1500/300',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(1500, reset($mapped));
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsYResolution()
    {
        $rawData = array(
            ImageMagick::YRESOLUTION => '1500/300',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(1500, reset($mapped));
    }

    #[Group('mapper')]
    public function testMapRawDataCorrectlyFormatsProjectionType()
    {
        $mapped = $this->mapper->mapRawData(array(
            ImageMagick::PROJECTIONTYPE => ' EquiRectangular ',
        ));

        $this->assertSame(array(Exif::PROJECTIONTYPE => 'equirectangular'), $mapped);
    }

    #[Group('mapper')]
    public function testMapRawDataIgnoresEmptyProjectionType()
    {
        $mapped = $this->mapper->mapRawData(array(
            ImageMagick::PROJECTIONTYPE => '  ',
        ));

        $this->assertSame(array(), $mapped);
    }

    /**
     * Data provider for testMapRawDataCorrectlyFormatsUsePanoramaViewer
     *
     * @return array
     */
    public static function providerUsePanoramaViewer()
    {
        return array(
            'string True'    => array('True', true),
            'string False'   => array('False', false),
            'string false'   => array('false', false),
            'string TRUE'    => array(' TRUE ', true),
            'string 1'       => array('1', true),
            'string 0'       => array('0', false),
        );
    }

    #[Group('mapper')]
    #[DataProvider('providerUsePanoramaViewer')]
    public function testMapRawDataCorrectlyFormatsUsePanoramaViewer($raw, $expected)
    {
        $mapped = $this->mapper->mapRawData(array(
            ImageMagick::USEPANORAMAVIEWER => $raw,
        ));

        $this->assertSame(array(Exif::USEPANORAMAVIEWER => $expected), $mapped);
    }

    #[Group('mapper')]
    public function testMapRawDataIgnoresInvalidUsePanoramaViewer()
    {
        foreach (array('abc', '', '2', 'yes') as $raw) {
            $mapped = $this->mapper->mapRawData(array(
                ImageMagick::USEPANORAMAVIEWER => $raw,
            ));

            $this->assertSame(array(), $mapped, 'value: ' . var_export($raw, true));
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
            array(ImageMagick::FULLPANOWIDTHPIXELS, Exif::FULLPANOWIDTHPIXELS),
            array(ImageMagick::FULLPANOHEIGHTPIXELS, Exif::FULLPANOHEIGHTPIXELS),
            array(ImageMagick::CROPPEDAREALEFTPIXELS, Exif::CROPPEDAREALEFTPIXELS),
            array(ImageMagick::CROPPEDAREATOPPIXELS, Exif::CROPPEDAREATOPPIXELS),
            array(ImageMagick::CROPPEDAREAIMAGEWIDTHPIXELS, Exif::CROPPEDAREAIMAGEWIDTHPIXELS),
            array(ImageMagick::CROPPEDAREAIMAGEHEIGHTPIXELS, Exif::CROPPEDAREAIMAGEHEIGHTPIXELS),
        );
    }

    #[Group('mapper')]
    #[DataProvider('providerPanoCropFields')]
    public function testMapRawDataCorrectlyFormatsPanoCropFields($field, $key)
    {
        foreach (array('8000', ' 8000 ') as $raw) {
            $mapped = $this->mapper->mapRawData(array($field => $raw));

            $this->assertSame(array($key => 8000), $mapped, 'value: ' . var_export($raw, true));
        }
    }

    #[Group('mapper')]
    #[DataProvider('providerPanoCropFields')]
    public function testMapRawDataIgnoresNonNumericPanoCropFields($field, $key)
    {
        foreach (array('abc', '', '8000px') as $raw) {
            $mapped = $this->mapper->mapRawData(array($field => $raw));

            $this->assertSame(array(), $mapped, 'value: ' . var_export($raw, true));
        }
    }

    /**
     * Data provider for testMapRawDataCorrectlyFormatsExposureBias
     *
     * @return array
     */
    public static function providerExposureBias()
    {
        return array(
            'rational -2/3' => array('-2/3', -0.67),
            'rational +1/3' => array('+1/3', 0.33),
            'rational 0/6' => array('0/6', 0.0),
            'rational 3/2' => array('3/2', 1.5),
        );
    }

    #[Group('mapper')]
    #[DataProvider('providerExposureBias')]
    public function testMapRawDataCorrectlyFormatsExposureBias($raw, $expected)
    {
        $mapped = $this->mapper->mapRawData(array(
            ImageMagick::EXPOSUREBIAS => $raw,
        ));

        $this->assertSame(array(Exif::EXPOSURE_BIAS => $expected), $mapped);
    }

    #[Group('mapper')]
    public function testMapRawDataIgnoresInvalidExposureBias()
    {
        foreach (array('abc', '', '1/0', '1/abc') as $raw) {
            $mapped = $this->mapper->mapRawData(array(
                ImageMagick::EXPOSUREBIAS => $raw,
            ));

            $this->assertSame(array(), $mapped, 'value: ' . var_export($raw, true));
        }
    }

    /**
     * Data provider for testMapRawDataCorrectlyFormatsWhiteBalance
     *
     * @return array
     */
    public static function providerWhiteBalance()
    {
        return array(
            'string 0' => array('0', 0),
            'string 1' => array('1', 1),
        );
    }

    #[Group('mapper')]
    #[DataProvider('providerWhiteBalance')]
    public function testMapRawDataCorrectlyFormatsWhiteBalance($raw, $expected)
    {
        $mapped = $this->mapper->mapRawData(array(
            ImageMagick::WHITEBALANCE => $raw,
        ));

        $this->assertSame(array(Exif::WHITE_BALANCE => $expected), $mapped);
    }

    #[Group('mapper')]
    public function testMapRawDataIgnoresInvalidWhiteBalance()
    {
        foreach (array('abc', '') as $raw) {
            $mapped = $this->mapper->mapRawData(array(
                ImageMagick::WHITEBALANCE => $raw,
            ));

            $this->assertSame(array(), $mapped, 'value: ' . var_export($raw, true));
        }
    }
}
