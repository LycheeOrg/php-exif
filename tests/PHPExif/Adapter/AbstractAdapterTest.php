<?php

use PHPExif\Adapter\AbstractAdapter;
use PHPExif\Adapter\Exiftool;
use PHPExif\Adapter\Native;
use PHPUnit\Framework\Attributes\Group;

class AbstractAdapterTest extends PHPUnit\Framework\TestCase
{
    /**
     * @var Exiftool|Native
     */
    protected Exiftool|Native $adapter;

    protected function setUp(): void
    {
        $this->adapter = new Native();
    }

    #[Group('adapter')]
    public function testSetOptionsReturnsCurrentInstance()
    {
        $result = $this->adapter->setOptions([]);
        $this->assertSame($this->adapter, $result);
    }

    #[Group('adapter')]
    public function testSetOptionsCorrectlySetsProperties()
    {
        $expected = array(
            'requiredSections'  => array('foo', 'bar', 'baz',),
            'includeThumbnail' => Native::INCLUDE_THUMBNAIL,
            'sectionsAsArrays' => Native::SECTIONS_AS_ARRAYS,
        );
        $this->adapter->setOptions($expected);

        foreach ($expected as $key => $value) {
            $reflProp = new \ReflectionProperty(Native::class, $key);
            $this->assertEquals($value, $reflProp->getValue($this->adapter));
        }
    }

    #[Group('adapter')]
    public function testSetOptionsIgnoresPropertiesWithoutSetters()
    {
        $expected = array(
            'iptcMapping' => array('foo', 'bar', 'baz'),
        );
        $this->adapter->setOptions($expected);

        foreach ($expected as $key => $value) {
            $reflProp = new \ReflectionProperty(Native::class, $key);
            $this->assertNotEquals($value, $reflProp->getValue($this->adapter));
        }
    }


    #[Group('adapter')]
    public function testConstructorSetsOptions()
    {
        $expected = array(
            'requiredSections'  => array('foo', 'bar', 'baz',),
            'includeThumbnail' => Native::INCLUDE_THUMBNAIL,
            'sectionsAsArrays' => Native::SECTIONS_AS_ARRAYS,
        );
        $adapter = new Native($expected);

        foreach ($expected as $key => $value) {
            $reflProp = new \ReflectionProperty(Native::class, $key);
            $this->assertEquals($value, $reflProp->getValue($adapter));
        }
    }

    #[Group('adapter')]
    public function testSetMapperReturnsCurrentInstance()
    {
        $mapper = new \PHPExif\Mapper\Native();
        $result = $this->adapter->setMapper($mapper);
        $this->assertSame($this->adapter, $result);
    }

    #[Group('adapter')]
    public function testSetMapperCorrectlySetsInProperty()
    {
        $mapper = new \PHPExif\Mapper\Native();
        $this->adapter->setMapper($mapper);

        $reflProp = new \ReflectionProperty(AbstractAdapter::class, 'mapper');
        $this->assertSame($mapper, $reflProp->getValue($this->adapter));
    }

    #[Group('adapter')]
    public function testGetMapperCorrectlyReturnsFromProperty()
    {
        $mapper = new \PHPExif\Mapper\Native();
        $reflProp = new \ReflectionProperty(AbstractAdapter::class, 'mapper');
        $reflProp->setValue($this->adapter, $mapper);
        $this->assertSame($mapper, $this->adapter->getMapper());
    }

    #[Group('adapter')]
    public function testGetMapperLazyLoadsMapperWhenNotPresent()
    {
        $reflProp = new \ReflectionProperty(
            get_class($this->adapter),
            'mapperClass'
        );

        $mapperClass = '\\PHPExif\\Mapper\\Native';
        $reflProp->setValue($this->adapter, $mapperClass);

        $this->assertInstanceOf($mapperClass, $this->adapter->getMapper());
    }

    #[Group('adapter')]
    public function testGetMapperLazyLoadingSetsInProperty()
    {
        $reflProp = new \ReflectionProperty(
            get_class($this->adapter),
            'mapperClass'
        );

        $mapperClass = '\\PHPExif\\Mapper\\Native';
        $reflProp->setValue($this->adapter, $mapperClass);

        $reflProp2 = new \ReflectionProperty(
            get_class($this->adapter),
            'mapper'
        );
        $this->adapter->getMapper();
        $this->assertInstanceOf($mapperClass, $reflProp2->getValue($this->adapter));
    }

    #[Group('adapter')]
    public function testSetHydratorReturnsCurrentInstance()
    {
        $hydrator = new \PHPExif\Hydrator\Mutator();
        $result = $this->adapter->setHydrator($hydrator);
        $this->assertSame($this->adapter, $result);
    }

    #[Group('adapter')]
    public function testSetHydratorCorrectlySetsInProperty()
    {
        $hydrator = new \PHPExif\Hydrator\Mutator();
        $this->adapter->setHydrator($hydrator);

        $reflProp = new \ReflectionProperty(AbstractAdapter::class, 'hydrator');
        $this->assertSame($hydrator, $reflProp->getValue($this->adapter));
    }

    #[Group('adapter')]
    public function testGetHydratorCorrectlyReturnsFromProperty()
    {
        $hydrator = new \PHPExif\Hydrator\Mutator();
        $reflProp = new \ReflectionProperty(AbstractAdapter::class, 'hydrator');
        $reflProp->setValue($this->adapter, $hydrator);
        $this->assertSame($hydrator, $this->adapter->getHydrator());
    }

    #[Group('adapter')]
    public function testGetHydratorLazyLoadsHydratorWhenNotPresent()
    {
        $hydratorClass = '\\PHPExif\\Hydrator\\Mutator';
        $this->assertInstanceOf($hydratorClass, $this->adapter->getHydrator());
    }

    #[Group('adapter')]
    public function testGetHydratorLazyLoadingSetsInProperty()
    {
        $hydratorClass = '\\PHPExif\\Hydrator\\Mutator';

        $reflProp = new \ReflectionProperty(
            get_class($this->adapter),
            'hydrator'
        );
        $this->adapter->getHydrator();
        $this->assertInstanceOf($hydratorClass, $reflProp->getValue($this->adapter));
    }
}
