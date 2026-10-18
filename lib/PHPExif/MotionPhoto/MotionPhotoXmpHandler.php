<?php

namespace PHPExif\MotionPhoto;

/**
 * SAX callbacks behind {@see MotionPhotoXmp::fromXml()}.
 *
 * XMP allows a property both as an attribute and as a child element, so each
 * property is read from either form:
 *
 *     <rdf:Description GCamera:MotionPhoto="1">
 *     <GCamera:MotionPhoto>1</GCamera:MotionPhoto>
 *
 * Names arrive as `<namespace URI>|<local name>`, so prefixes do not matter.
 *
 * @internal
 */
final class MotionPhotoXmpHandler
{
    public const SEPARATOR = '|';

    private const MOTION_PHOTO = MotionPhotoXmp::NS_CAMERA . self::SEPARATOR . 'MotionPhoto';
    private const MICRO_VIDEO_OFFSET = MotionPhotoXmp::NS_CAMERA . self::SEPARATOR . 'MicroVideoOffset';
    private const CONTAINER_ITEM = MotionPhotoXmp::NS_CONTAINER . self::SEPARATOR . 'Item';
    private const ITEM_SEMANTIC = MotionPhotoXmp::NS_ITEM . self::SEPARATOR . 'Semantic';
    private const ITEM_LENGTH = MotionPhotoXmp::NS_ITEM . self::SEPARATOR . 'Length';
    private const SEMANTIC_MOTION_PHOTO = 'MotionPhoto';
    private const CAMERA_PROPERTIES = [self::MOTION_PHOTO => true, self::MICRO_VIDEO_OFFSET => true];
    private const ITEM_PROPERTIES = [self::ITEM_SEMANTIC => true, self::ITEM_LENGTH => true];

    /** @var array<string,string> raw values by qualified name: the two camera properties */
    private array $camera = [];

    /** @var array<string,string>|null raw `Item:*` values of the container item being read */
    private ?array $item = null;

    private ?string $videoItemLength = null;

    /** qualified name of the element whose text is being collected */
    private ?string $textOf = null;
    private string $text = '';

    /**
     * @param array<string,string> $attributes
     */
    public function start(\XMLParser $parser, string $name, array $attributes): void
    {
        $this->camera += array_intersect_key($attributes, self::CAMERA_PROPERTIES);
        $this->item = $name === self::CONTAINER_ITEM
            ? array_intersect_key($attributes, self::ITEM_PROPERTIES)
            : $this->item;
        $this->textOf = $name;
        $this->text = '';
    }

    public function text(\XMLParser $parser, string $data): void
    {
        $this->text .= $data;
    }

    public function end(\XMLParser $parser, string $name): void
    {
        $value = trim($this->text);
        $isTextElement = $this->textOf === $name;
        $this->textOf = null;
        $this->text = '';

        if ($isTextElement && in_array($name, [self::MOTION_PHOTO, self::MICRO_VIDEO_OFFSET], true)) {
            $this->camera[$name] ??= $value;
        }
        if ($isTextElement && $this->item !== null && in_array($name, [self::ITEM_SEMANTIC, self::ITEM_LENGTH], true)) {
            $this->item[$name] ??= $value;
        }
        if ($name === self::CONTAINER_ITEM) {
            $this->closeItem();
        }
    }

    public function result(): MotionPhotoXmp
    {
        return new MotionPhotoXmp(
            isMotionPhoto: trim($this->camera[self::MOTION_PHOTO] ?? '') === '1',
            itemLength: self::toLength($this->videoItemLength),
            legacyOffset: self::toLength($this->camera[self::MICRO_VIDEO_OFFSET] ?? null),
        );
    }

    private function closeItem(): void
    {
        $isVideo = ($this->item[self::ITEM_SEMANTIC] ?? null) === self::SEMANTIC_MOTION_PHOTO;
        $this->videoItemLength ??= $isVideo ? ($this->item[self::ITEM_LENGTH] ?? null) : null;
        $this->item = null;
    }

    /**
     * @return int the value if it is a plain non-negative decimal integer, `0` otherwise
     */
    private static function toLength(?string $raw): int
    {
        $value = trim($raw ?? '');

        return ctype_digit($value) ? (int) $value : 0;
    }
}
