<?php

use PHPExif\Adapter\Exiftool;
use PHPExif\Adapter\FFprobe;
use PHPExif\Adapter\ImageMagick;
use PHPExif\Adapter\Native;
use PHPExif\Contracts\AdapterInterface;
use PHPExif\Exif;
use PHPExif\Reader\Reader;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPExif\Reader\Reader
     */
    protected Reader $reader;

    /**
     * Setup function before the tests
     */
    public function setUp() : void
    {
        /** @var AdapterInterface $adapter */
        $adapter = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $this->reader = new \PHPExif\Reader\Reader($adapter);
    }

    /**
     * @group reader
     */
    public function testConstructorWithAdapter()
    {
        /** @var AdapterInterface */
        $mock = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');
        $reflProperty->setAccessible(true);

        $reader = new \PHPExif\Reader\Reader($mock);

        $this->assertSame($mock, $reflProperty->getValue($reader));
    }

    /**
     * @group reader
     */
    public function testGetExifPassedToAdapter()
    {
        $adapter = $this->getMockBuilder(AdapterInterface::class)->getMockForAbstractClass();
        $adapter->expects($this->once())->method('getExifFromFile');

        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');
        $reflProperty->setAccessible(true);
        $reflProperty->setValue($this->reader, $adapter);

        $this->reader->read('/tmp/foo.bar');
    }

    /**
     * @group reader
     */
    public function testFactoryThrowsException()
    {
        $this->expectException('TypeError');
        \PHPExif\Reader\Reader::factory('foo');
    }

    /**
     * @group reader
     */
    public function testFactoryReturnsCorrectType()
    {
        $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::NATIVE);

        $this->assertInstanceOf(Reader::class, $reader);
    }

    /**
     * @group reader
     */
    public function testFactoryAdapterTypeNative()
    {
        $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::NATIVE);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');
        $reflProperty->setAccessible(true);

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(Native::class, $adapter);
    }

    /**
     * @group reader
     */
    public function testFactoryAdapterTypeExiftool()
    {
        $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::EXIFTOOL);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');
        $reflProperty->setAccessible(true);

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(Exiftool::class, $adapter);
    }

    /**
     * @group reader
     */
    public function testFactoryAdapterTypeFFprobe()
    {
        $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::FFPROBE);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');
        $reflProperty->setAccessible(true);

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(FFprobe::class, $adapter);
    }


    /**
     * @group reader
     */
    public function testFactoryAdapterTypeImageMagick()
    {
        $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::IMAGICK);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');
        $reflProperty->setAccessible(true);

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(ImageMagick::class, $adapter);
    }

    /**
     * @group reader
     */
    public function testGetExifFromFileCallsReadMethod()
    {
        /** @var MockObject<Reader> $mock */
        $mock = $this->getMockBuilder(Reader::class)
            ->onlyMethods(array('read'))
            ->disableOriginalConstructor()
            ->getMock();

        $expected = '/foo/bar/baz';
        $expectedResult = new Exif([]);

        $mock->expects($this->once())
            ->method('read')
            ->with($this->equalTo($expected))
            ->will($this->returnValue($expectedResult));

        $result = $mock->getExifFromFile($expected);
        $this->assertEquals($expectedResult, $result);
    }
}
