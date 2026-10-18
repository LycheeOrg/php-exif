<?php

use PHPExif\MotionPhoto\MotionPhotoXmp;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Covers reading the Google camera and container XMP of motion
 * photos.
 */
class MotionPhotoXmpTest extends TestCase
{
    /**
     * The standard XMP packet of a Pixel 11 Pro Ultra HDR motion photo
     * verbatim except for the maker note, which was stripped.
     */
    private const PIXEL_XMP = <<<'XML'
        <x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 5.1.0-jc003">
          <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
            <rdf:Description rdf:about=""
                xmlns:hdrgm="http://ns.adobe.com/hdr-gain-map/1.0/"
                xmlns:GCamera="http://ns.google.com/photos/1.0/camera/"
                xmlns:Container="http://ns.google.com/photos/1.0/container/"
                xmlns:Item="http://ns.google.com/photos/1.0/container/item/"
              hdrgm:Version="1.0"
              GCamera:GFileMetadata="SgQIABAB"
              GCamera:MotionPhoto="1"
              GCamera:MotionPhotoVersion="1"
              GCamera:MotionPhotoPresentationTimestampUs="1166896">
              <Container:Directory>
                <rdf:Seq>
                  <rdf:li rdf:parseType="Resource">
                    <Container:Item
                      Item:Mime="image/jpeg"
                      Item:Semantic="Primary"/>
                  </rdf:li>
                  <rdf:li rdf:parseType="Resource">
                    <Container:Item
                      Item:Length="1753"
                      Item:Mime="image/jpeg"
                      Item:Semantic="GainMap"/>
                  </rdf:li>
                  <rdf:li rdf:parseType="Resource">
                    <Container:Item
                      Item:Mime="video/mp4"
                      Item:Semantic="MotionPhoto"
                      Item:Length="3725212"
                      Item:Padding="0"/>
                  </rdf:li>
                </rdf:Seq>
              </Container:Directory>
            </rdf:Description>
          </rdf:RDF>
        </x:xmpmeta>
        XML;

    #[Group('motionphoto')]
    public function testPixelUltraHdrMotionPhoto(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::PIXEL_XMP);

        self::assertTrue($xmp->isMotionPhoto);
        self::assertSame(3725212, $xmp->itemLength);
        self::assertSame(0, $xmp->legacyOffset);
    }

    #[Group('motionphoto')]
    public function testMotionPhotoWithoutGainMap(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::motionPhoto(
            self::item('Primary') . self::item('MotionPhoto', 'Item:Length="10705" Item:Padding="0"')
        ));

        self::assertTrue($xmp->isMotionPhoto);
        self::assertSame(10705, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testLengthBeforeSemantic(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::motionPhoto('<rdf:li rdf:parseType="Resource"><Container:Item Item:Length="42" Item:Semantic="MotionPhoto"/></rdf:li>'));

        self::assertSame(42, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testMotionPhotoFlagWithoutVideoItem(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::motionPhoto(self::item('Primary')));

        self::assertTrue($xmp->isMotionPhoto);
        self::assertSame(0, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testMotionPhotoFlagWithoutDirectory(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::description('GCamera:MotionPhoto="1"', ''));

        self::assertTrue($xmp->isMotionPhoto);
        self::assertSame(0, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testVideoItemWithoutLength(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::motionPhoto(self::item('Primary') . self::item('MotionPhoto')));

        self::assertSame(0, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testOnlyTheMotionPhotoItemLengthCounts(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::motionPhoto(self::item('GainMap', 'Item:Length="1753"')));

        self::assertSame(0, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testLegacyMicroVideo(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::description('GCamera:MicroVideo="1" GCamera:MicroVideoVersion="1" GCamera:MicroVideoOffset="4495341" GCamera:MicroVideoPresentationTimestampUs="1366016"', ''));

        self::assertFalse($xmp->isMotionPhoto);
        self::assertSame(0, $xmp->itemLength);
        self::assertSame(4495341, $xmp->legacyOffset);
    }

    #[Group('motionphoto')]
    public function testElementSyntax(): void
    {
        $body = '<GCamera:MotionPhoto>1</GCamera:MotionPhoto>' .
            '<Container:Directory><rdf:Seq><rdf:li rdf:parseType="Resource"><Container:Item>' .
            '<Item:Semantic>MotionPhoto</Item:Semantic><Item:Length> 777 </Item:Length>' .
            '</Container:Item></rdf:li></rdf:Seq></Container:Directory>';
        $xmp = MotionPhotoXmp::fromXml(self::description('', $body));

        self::assertTrue($xmp->isMotionPhoto);
        self::assertSame(777, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testLegacyOffsetElementSyntax(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::description('', '<GCamera:MicroVideoOffset>1234</GCamera:MicroVideoOffset>'));

        self::assertSame(1234, $xmp->legacyOffset);
    }

    #[Group('motionphoto')]
    public function testNamespaceIsMatchedByUriNotPrefix(): void
    {
        $xml = '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
            '<rdf:Description xmlns:Cam="http://ns.google.com/photos/1.0/camera/" xmlns:C="http://ns.google.com/photos/1.0/container/" xmlns:I="http://ns.google.com/photos/1.0/container/item/" Cam:MotionPhoto="1">' .
            '<C:Directory><rdf:Seq><rdf:li rdf:parseType="Resource"><C:Item I:Semantic="MotionPhoto" I:Length="99"/></rdf:li></rdf:Seq></C:Directory>' .
            '</rdf:Description></rdf:RDF></x:xmpmeta>';
        $xmp = MotionPhotoXmp::fromXml($xml);

        self::assertTrue($xmp->isMotionPhoto);
        self::assertSame(99, $xmp->itemLength);
    }

    #[Group('motionphoto')]
    public function testWrongNamespaceIsIgnored(): void
    {
        $xml = '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
            '<rdf:Description xmlns:GCamera="http://example.com/not-google/" GCamera:MotionPhoto="1" GCamera:MicroVideoOffset="10"/>' .
            '</rdf:RDF></x:xmpmeta>';

        self::assertNone(MotionPhotoXmp::fromXml($xml));
    }

    #[Group('motionphoto')]
    public function testMotionPhotoFlagMustBeOne(): void
    {
        self::assertFalse(MotionPhotoXmp::fromXml(self::description('GCamera:MotionPhoto="0"', ''))->isMotionPhoto);
    }

    #[Group('motionphoto')]
    public function testInvalidNumbersReadAsZero(): void
    {
        $xmp = MotionPhotoXmp::fromXml(self::description(
            'GCamera:MotionPhoto="1" GCamera:MicroVideoOffset="-5"',
            '<Container:Directory><rdf:Seq>' . self::item('MotionPhoto', 'Item:Length="12abc"') . '</rdf:Seq></Container:Directory>'
        ));

        self::assertSame(0, $xmp->itemLength);
        self::assertSame(0, $xmp->legacyOffset);
    }

    #[Group('motionphoto')]
    public function testUnrelatedXmp(): void
    {
        $xml = '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
            '<rdf:Description xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:subject><rdf:Bag><rdf:li>Mongolia</rdf:li></rdf:Bag></dc:subject></rdf:Description>' .
            '</rdf:RDF></x:xmpmeta>';

        self::assertNone(MotionPhotoXmp::fromXml($xml));
    }

    #[Group('motionphoto')]
    public function testMalformedXml(): void
    {
        self::assertNone(MotionPhotoXmp::fromXml(substr(self::PIXEL_XMP, 0, 900)));
    }

    #[Group('motionphoto')]
    public function testEmptyString(): void
    {
        self::assertNone(MotionPhotoXmp::fromXml(''));
    }

    #[Group('motionphoto')]
    public function testExternalEntitiesAreNotResolved(): void
    {
        $xml = '<?xml version="1.0"?><!DOCTYPE x [<!ENTITY ext SYSTEM "file:///etc/hosts">]>' .
            '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
            '<rdf:Description xmlns:GCamera="http://ns.google.com/photos/1.0/camera/"><GCamera:MicroVideoOffset>&ext;</GCamera:MicroVideoOffset></rdf:Description>' .
            '</rdf:RDF></x:xmpmeta>';

        self::assertSame(0, MotionPhotoXmp::fromXml($xml)->legacyOffset);
    }

    private static function assertNone(MotionPhotoXmp $xmp): void
    {
        self::assertFalse($xmp->isMotionPhoto);
        self::assertSame(0, $xmp->itemLength);
        self::assertSame(0, $xmp->legacyOffset);
    }

    private static function item(string $semantic, string $extra = ''): string
    {
        return '<rdf:li rdf:parseType="Resource"><Container:Item Item:Mime="x/y" Item:Semantic="' . $semantic . '" ' . $extra . '/></rdf:li>';
    }

    private static function motionPhoto(string $items): string
    {
        return self::description('GCamera:MotionPhoto="1" GCamera:MotionPhotoVersion="1"', '<Container:Directory><rdf:Seq>' . $items . '</rdf:Seq></Container:Directory>');
    }

    private static function description(string $attributes, string $body): string
    {
        return '<x:xmpmeta xmlns:x="adobe:ns:meta/"><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
            '<rdf:Description rdf:about=""' .
            ' xmlns:GCamera="http://ns.google.com/photos/1.0/camera/"' .
            ' xmlns:Container="http://ns.google.com/photos/1.0/container/"' .
            ' xmlns:Item="http://ns.google.com/photos/1.0/container/item/" ' .
            $attributes . '>' . $body . '</rdf:Description></rdf:RDF></x:xmpmeta>';
    }
}
