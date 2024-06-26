<?php

use PHPExif\Contracts\MapperInterface;
use PhpExif\Mapper\Exiftool;

class ExiftoolMapperTest extends \PHPUnit\Framework\TestCase
{
    protected $mapper;

    public function setUp(): void
    {
        $this->mapper = new Exiftool();
    }

    /**
     * @group mapper
     */
    public function testClassImplementsCorrectInterface()
    {
        $this->assertInstanceOf(MapperInterface::class, $this->mapper);
    }

    /**
     * @group mapper
     */
    public function testMapRawDataIgnoresFieldIfItDoesntExist()
    {
        $rawData = array('foo' => 'bar');
        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertCount(0, $mapped);
    }

    /**
     * @group mapper
     */
    public function testMapRawDataMapsFieldsCorrectly()
    {
        $reflProp = new \ReflectionProperty(get_class($this->mapper), 'map');
        $reflProp->setAccessible(true);
        $map = $reflProp->getValue($this->mapper);

        // ignore custom formatted data stuff:
        unset($map[Exiftool::APERTURE]);
        unset($map[Exiftool::APPROXIMATEFOCUSDISTANCE]);
        unset($map[Exiftool::COPYRIGHT_IPTC]);
        unset($map[Exiftool::DATETIMEORIGINAL]);
        unset($map[Exiftool::EXPOSURETIME]);
        unset($map[Exiftool::FOCALLENGTH]);
        unset($map[Exiftool::GPSLATITUDE]);
        unset($map[Exiftool::GPSLONGITUDE]);
        unset($map[Exiftool::CAPTIONABSTRACT]);
        unset($map[Exiftool::TITLE]);
        unset($map[Exiftool::DESCRIPTION_XMP]);
        unset($map[Exiftool::CONTENTIDENTIFIER]);
        unset($map[Exiftool::KEYWORDS]);
        unset($map[Exiftool::DATETIMEORIGINAL]);
        unset($map[Exiftool::DATETIMEORIGINAL_QUICKTIME]);
        unset($map[Exiftool::DATETIMEORIGINAL_AVI]);
        unset($map[Exiftool::DATETIMEORIGINAL_WEBM]);
        unset($map[Exiftool::DATETIMEORIGINAL_OGG]);
        unset($map[Exiftool::DATETIMEORIGINAL_WMV]);
        unset($map[Exiftool::DATETIMEORIGINAL_APPLE]);
        unset($map[Exiftool::DATETIMEORIGINAL_PNG]);
        unset($map[Exiftool::MAKE_QUICKTIME]);
        unset($map[Exiftool::MODEL_QUICKTIME]);
        unset($map[Exiftool::FRAMERATE]);
        unset($map[Exiftool::FRAMERATE_QUICKTIME_1]);
        unset($map[Exiftool::FRAMERATE_QUICKTIME_2]);
        unset($map[Exiftool::FRAMERATE_QUICKTIME_3]);
        unset($map[Exiftool::FRAMERATE_AVI]);
        unset($map[Exiftool::FRAMERATE_OGG]);
        unset($map[Exiftool::DURATION]);
        unset($map[Exiftool::DURATION_QUICKTIME]);
        unset($map[Exiftool::DURATION_WEBM]);
        unset($map[Exiftool::DURATION_WMV]);
        unset($map[Exiftool::CONTENTIDENTIFIER_KEYS]);
        unset($map[Exiftool::GPSLATITUDE_QUICKTIME]);
        unset($map[Exiftool::GPSLONGITUDE_QUICKTIME]);
        unset($map[Exiftool::GPSALTITUDE_QUICKTIME]);
        unset($map[Exiftool::MEDIA_GROUP_UUID]);
        unset($map[Exiftool::MICROVIDEOOFFSET]);
        unset($map[Exiftool::CITY]);
        unset($map[Exiftool::SUBLOCATION]);
        unset($map[Exiftool::STATE]);
        unset($map[Exiftool::COUNTRY]);
        unset($map[Exiftool::LENS_ID]);
        unset($map[Exiftool::LENS]);
        unset($map[Exiftool::DESCRIPTION]);
        unset($map[Exiftool::KEYWORDS]);
        unset($map[Exiftool::SUBJECT]);
        unset($map[Exiftool::CONTENTIDENTIFIER]);
        unset($map[Exiftool::CONTENTIDENTIFIER_QUICKTIME]);

        // create raw data
        $keys = array_keys($map);
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

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsAperture()
    {
        $rawData = array(
            Exiftool::APERTURE => 0.123,
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals('f/0.1', reset($mapped));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsFocusDistance()
    {
        $rawData = array(
            Exiftool::APPROXIMATEFOCUSDISTANCE => 50,
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals('50m', reset($mapped));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsCreationDate()
    {
        $rawData = array(
            Exiftool::DATETIMEORIGINAL => '2015:04:01 12:11:09',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $result = reset($mapped);
        $this->assertInstanceOf('\\DateTime', $result);
        $this->assertEquals(
            reset($rawData),
            $result->format('Y:m:d H:i:s')
        );
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsCreationDateWithTimeZone()
    {
        $data = array(
          array(
            Exiftool::DATETIMEORIGINAL => '2015:04:01 12:11:09+0200',
          ),
          array(
              Exiftool::DATETIMEORIGINAL => '2015:04:01 12:11:09',
              'ExifIFD:OffsetTimeOriginal' => '+0200',
          ),
          array(
            Exiftool::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'ExifIFD:OffsetTime' => '+0200',
          ),
          array(
              Exiftool::DATETIMEORIGINAL_APPLE => '2015-04-01T12:11:09+0200',
              Exiftool::DATETIMEORIGINAL => '2015:04:01 12:11:09',
              'ExifIFD:OffsetTimeOriginal' => '+0200',
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

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsCreationDateWithTimeZone2()
    {
        $rawData = array(
            Exiftool::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'ExifIFD:OffsetTimeOriginal' => '+0200',
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

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyIgnoresIncorrectCreationDate()
    {
        $rawData = array(
            Exiftool::DATETIMEORIGINAL => '2015:04:01',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(false, reset($mapped));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyIgnoresIncorrectCreationDate2()
    {
        $rawData = array(
            Exiftool::DATETIMEORIGINAL_APPLE => '2015:04:01',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(false, reset($mapped));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyIgnoresIncorrectTimeZone()
    {
        $rawData = array(
            Exiftool::DATETIMEORIGINAL => '2015:04:01 12:11:09',
            'ExifIFD:OffsetTimeOriginal' => '   :  ',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $result = reset($mapped);
        $this->assertInstanceOf('\\DateTime', $result);
        $this->assertEquals(
            '2015:04:01 12:11:09',
            $result->format('Y:m:d H:i:s')
        );
    }

    /**
     * @group mapper
     */
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
                Exiftool::EXPOSURETIME => $value,
            ));

            $this->assertEquals($expected, reset($mapped));
        }
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsFocalLength()
    {
        $rawData = array(
            Exiftool::FOCALLENGTH => '15 m',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(15, reset($mapped));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsGPSData()
    {
        $this->mapper->setNumeric(false);
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSLATITUDE  => '40 deg 20\' 0.42857" N',
                'GPS:GPSLatitudeRef'                   => 'North',
                Exiftool::GPSLONGITUDE => '20 deg 10\' 2.33333" W',
                'GPS:GPSLongitudeRef'                  => 'West',
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

    /**
     * @group mapper
     */
    public function testMapRawDataIncorrectlyFormatedGPSData()
    {
        $this->mapper->setNumeric(false);
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSLATITUDE  => '40 degrees 20\' 0.42857" N',
                'GPS:GPSLatitudeRef'                   => 'North',
                Exiftool::GPSLONGITUDE => '20 degrees 10\' 2.33333" W',
                'GPS:GPSLongitudeRef'                  => 'West',
            )
        );

        $this->assertCount(0, $result);
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsNumericGPSData()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSLATITUDE  => '40.333452381',
                'GPS:GPSLatitudeRef'                   => 'North',
                Exiftool::GPSLONGITUDE => '20.167314814',
                'GPS:GPSLongitudeRef'                  => 'West',
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

    /**
     * @group mapper
     */
    public function testMapRawDataOnlyLatitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSLATITUDE => '40.333452381',
                'GPS:GPSLatitudeRef'                  => 'North',
            )
        );

        $this->assertCount(1, $result);
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyIgnoresEmptyGPSData()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSLATITUDE  => '',
                'GPS:GPSLatitudeRef'                   => '',
                Exiftool::GPSLONGITUDE => '',
                'GPS:GPSLongitudeRef'                  => '',
            )
        );

        $this->assertEquals(false, reset($result));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyIgnoresIncorrectImageDirection()
    {
        $rawData = array(
            Exiftool::IMGDIRECTION => 'undef',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(false, reset($mapped));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectImageDirection()
    {
        $rawData = array(
            Exiftool::IMGDIRECTION => '180.0',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals('180.0', reset($mapped));
    }

    /**
     * @group mapper
     */
    public function testSetNumericInProperty()
    {
        $reflProperty = new \ReflectionProperty(get_class($this->mapper), 'numeric');
        $reflProperty->setAccessible(true);

        $expected = true;
        $this->mapper->setNumeric($expected);

        $this->assertEquals($expected, $reflProperty->getValue($this->mapper));
    }

    public function testMapRawDataCorrectlyFormatsDifferentDateTimeString()
    {
        $rawData = array(
            Exiftool::DATETIMEORIGINAL => '2014-12-15 00:12:00'
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
            Exiftool::DATETIMEORIGINAL => 'Invalid Date String'
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

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyAltitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSALTITUDE  => '122.053',
                'GPS:GPSAltitudeRef'                   => '0',
            )
        );
        $expected = 122.053;
        $this->assertEquals($expected, reset($result));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyNegativeAltitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSALTITUDE  => '122.053',
                'GPS:GPSAltitudeRef'                   => '1',
            )
        );
        $expected = '-122.053';
        $this->assertEquals($expected, reset($result));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyIgnoresIncorrectAltitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSALTITUDE  => 'undef',
                'GPS:GPSAltitudeRef'                   => '0',
            )
        );
        $this->assertEquals(false, reset($result));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyFormatsQuicktimeGPSData()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSLATITUDE_QUICKTIME  => '40.333',
                'GPS:GPSLatitudeRef'                             => 'North',
                Exiftool::GPSLONGITUDE_QUICKTIME => '-20.167',
                'GPS:GPSLongitudeRef'                            => 'West',
            )
        );
        $expected_gps = '40.333,-20.167';
        $expected_lat = '40.333';
        $expected_lon = '-20.167';
        $this->assertCount(3, $result);
        $this->assertEquals($expected_gps, $result['gps']);
        $this->assertEquals($expected_lat, $result['latitude']);
        $this->assertEquals($expected_lon, $result['longitude']);
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyQuicktimeAltitude()
    {
        $result = $this->mapper->mapRawData(
            array(
                Exiftool::GPSALTITUDE_QUICKTIME  => '122.053',
                'Composite:GPSAltitudeRef'                       => '1',
            )
        );
        $expected = -122.053;
        $this->assertEquals($expected, reset($result));
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyHeightVideo()
    {
        $rawData = array(
          '600'  => array(
                            Exiftool::IMAGEHEIGHT_VIDEO  => '800x600',
                        ),
          '600'  => array(
                            Exiftool::IMAGEHEIGHT_VIDEO  => '800x600',
                            'Composite:Rotation'                        => '0',
                        ),
          '800'  => array(
                            Exiftool::IMAGEHEIGHT_VIDEO  => '800x600',
                            'Composite:Rotation'                        => '90',
                       ),
          '800'  => array(
                            Exiftool::IMAGEHEIGHT_VIDEO  => '800x600',
                            'Composite:Rotation'                        => '270',
                        ),
          '600'  => array(
                            Exiftool::IMAGEHEIGHT_VIDEO  => '800x600',
                            'Composite:Rotation'                        => '360',
                        ),
          '600'  => array(
                            Exiftool::IMAGEHEIGHT_VIDEO  => '800x600',
                            'Composite:Rotation'                        => '180',
                        ),
          '600'  => array(
                            Exiftool::IMAGEHEIGHT_VIDEO  => '800 600',
                            'Composite:Rotation'                        => '180',
                        ),
      );

        foreach ($rawData as $expected => $value) {
            $mapped = $this->mapper->mapRawData($value);

            $this->assertEquals($expected, $mapped['height']);
        }
    }



    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyWidthVideo()
    {
        $rawData = array(
              '800'  => array(
                                Exiftool::IMAGEWIDTH_VIDEO  => '800x600',
                            ),
              '800'  => array(
                                Exiftool::IMAGEWIDTH_VIDEO  => '800x600',
                                'Composite:Rotation'                        => '0',
                            ),
              '600'  => array(
                                Exiftool::IMAGEWIDTH_VIDEO  => '800x600',
                                'Composite:Rotation'                        => '90',
                            ),
              '600'  => array(
                                Exiftool::IMAGEWIDTH_VIDEO  => '800x600',
                                'Composite:Rotation'                        => '270',
                            ),
              '800'  => array(
                                Exiftool::IMAGEWIDTH_VIDEO  => '800x600',
                                'Composite:Rotation'                        => '360',
                            ),
              '800'  => array(
                                Exiftool::IMAGEWIDTH_VIDEO  => '800x600',
                                'Composite:Rotation'                        => '180',
                            ),
              '800'  => array(
                                Exiftool::IMAGEWIDTH_VIDEO  => '800 600',
                                'Composite:Rotation'                        => '180',
                            ),
          );

        foreach ($rawData as $expected => $value) {
            $mapped = $this->mapper->mapRawData($value);

            $this->assertEquals($expected, $mapped['width']);
        }
    }


    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyIsoFormats()
    {
        $expected = array(
                '80' => array(
                    'ExifIFD:ISO'     => '80',
                ),
                '800' => array(
                    'ExifIFD:ISO'     => '800 0 0',
                ),
            );

        foreach ($expected as $key => $value) {
            $result = $this->mapper->mapRawData($value);
            $this->assertEquals($key, reset($result));
        }
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyLensData()
    {
        $data = array(
            array(
                Exiftool::LENS => 'LEICA DG 12-60/F2.8-4.0',
            ),
            array(
                Exiftool::LENS => 'LEICA DG 12-60/F2.8-4.0',
                Exiftool::LENS_ID => 'LUMIX G VARIO 12-32/F3.5-5.6',
            ),
            array(
                Exiftool::LENS_ID => 'LUMIX G VARIO 12-32/F3.5-5.6',
                Exiftool::LENS => 'LEICA DG 12-60/F2.8-4.0',
          )
        );

        foreach ($data as $key => $rawData) {
            $mapped = $this->mapper->mapRawData($rawData);

            $this->assertEquals(
                'LEICA DG 12-60/F2.8-4.0',
                reset($mapped)
            );
        }
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyLensData2()
    {
        $rawData = array(
            Exiftool::LENS_ID => 'LUMIX G VARIO 12-32/F3.5-5.6',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(
            'LUMIX G VARIO 12-32/F3.5-5.6',
            reset($mapped)
        );
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyKeywords()
    {
        $rawData = array(
            Exiftool::KEYWORDS => 'Keyword_1 Keyword_2',
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(
            ['Keyword_1 Keyword_2'],
            reset($mapped)
        );
    }

    /**
     * @group mapper
     */
    public function testMapRawDataCorrectlyKeywordsAndSubject()
    {
        $rawData = array(
            Exiftool::KEYWORDS => array('Keyword_1', 'Keyword_2'),
            Exiftool::SUBJECT => array('Keyword_1', 'Keyword_3'),
        );

        $mapped = $this->mapper->mapRawData($rawData);

        $this->assertEquals(
            array('Keyword_1' ,'Keyword_2', 'Keyword_3'),
            reset($mapped)
        );
    }
}
