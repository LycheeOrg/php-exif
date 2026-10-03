<?php

use PHPExif\Adapter\Exiftool;
use PHPExif\Adapter\FFprobe;
use PHPExif\Adapter\ImageMagick;
use PHPExif\Adapter\Native;
use PHPExif\Contracts\AdapterInterface;
use PHPExif\Exif;
use PHPExif\Reader\Reader;
use PHPUnit\Framework\Attributes\Group;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    #[Group('reader')]
    public function testConstructorWithAdapter()
    {
        /** @var AdapterInterface $mock */
        $mock = $this->createStub(AdapterInterface::class);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');

        $reader = new Reader($mock);

        $this->assertSame($mock, $reflProperty->getValue($reader));
    }

    #[Group('reader')]
    public function testGetExifPassedToAdapter()
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->expects($this->once())->method('getExifFromFile');
        $reader = new Reader($adapter);
        $reader->read('/tmp/foo.bar');
    }

    #[Group('reader')]
    public function testFactoryThrowsException()
    {
        $this->expectException('TypeError');
        Reader::factory('foo');
    }

    #[Group('reader')]
    public function testFactoryReturnsCorrectType()
    {
        $reader = Reader::factory(\PHPExif\Enum\ReaderType::NATIVE);

        $this->assertInstanceOf(Reader::class, $reader);
    }

    #[Group('reader')]
    public function testFactoryAdapterTypeNative()
    {
        $reader = Reader::factory(\PHPExif\Enum\ReaderType::NATIVE);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(Native::class, $adapter);
    }

    #[Group('reader')]
    public function testFactoryAdapterTypeExiftool()
    {
        $reader = Reader::factory(\PHPExif\Enum\ReaderType::EXIFTOOL);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(Exiftool::class, $adapter);
    }

    #[Group('reader')]
    public function testFactoryAdapterTypeFFprobe()
    {
        $reader = Reader::factory(\PHPExif\Enum\ReaderType::FFPROBE);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(FFprobe::class, $adapter);
    }


    #[Group('reader')]
    public function testFactoryAdapterTypeImageMagick()
    {
        $reader = Reader::factory(\PHPExif\Enum\ReaderType::IMAGICK);
        $reflProperty = new \ReflectionProperty(Reader::class, 'adapter');

        $adapter = $reflProperty->getValue($reader);

        $this->assertInstanceOf(ImageMagick::class, $adapter);
    }
}
