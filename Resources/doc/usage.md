## Usage ##

### Using factory method

```php
<?php
// reader with Native adapter
$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::NATIVE);

// reader with Exiftool adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::EXIFTOOL);

// reader with FFmpeg/FFprobe adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::FFPROBE);

// reader with Imagick adapter
//$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::IMAGICK);

$exif = $reader->read('/path/to/file');

echo 'Title: ' . $exif->getTitle() . PHP_EOL;
```

### Using custom options

```php
<?php
$adapter = new \PHPExif\Adapter\Exiftool(
    array(
        'toolPath'  => '/path/to/exiftool',
    )
);
$reader = new \PHPExif\Reader\Reader($adapter);

$exif = $reader->read('/path/to/file');

echo 'Title: ' . $exif->getTitle() . PHP_EOL;
```

### Google Motion Photos

A Google Motion Photo is a JPEG with a short video appended to it. The reader
reports the length of that video, in bytes counted from the end of the file,
as `MicroVideoOffset`, whatever the adapter:

```php
<?php
$reader = \PHPExif\Reader\Reader::factory(\PHPExif\Enum\ReaderType::NATIVE);
$exif = $reader->read('/path/to/PXL_20261015_222940892.MP.jpg');

$videoLength = $exif->getMicroVideoOffset(); // false if the file has no video
```

Both formats are supported: the legacy `GCamera:MicroVideoOffset` (Android 10
and earlier) and Motion Photo 1.0 (`GCamera:MotionPhoto` with a
`Container:Directory`, Android 11 and later, Ultra HDR files included). The
XMP is read from the JPEG itself by `\PHPExif\MotionPhoto\MotionPhotoLocator`,
which can also be used on its own:

```php
<?php
$videoLength = \PHPExif\MotionPhoto\MotionPhotoLocator::locate('/path/to/file.jpg'); // 0 if none
```
