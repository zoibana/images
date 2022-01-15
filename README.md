# Zoibana\images

It's just a personal image helper (using GD library). Pull Requests and Issues are welcome!

## Create image object

```php

/** Create from file */
$image = ImageResource::createFromFile('path/to/image.png');

/* Or create new */
$image = ImageResource::create($width, $height);

```

## Get properties of image

```php

/* Get dimmensions */
[$width, $height] = $image->getSizes();

/* Get image type using default PHP IMAGETYPE_* constants */
$imagetype = $image->getImageType();

/* Get resource of image */
$resource = $image->getResource();

```

## Manupulate image

```php

/* Change image type */
$newImage = $image->setImageType(IMAGETYPE_WEBP);

/** Crop image */
$image->resize($width, $height, Resize::ACTION_CROP);

/** Scale image */
$image->scale($width, $height, Resize::ACTION_SCALE);

/** Rotate JPEG to correct orientation using EXIF data */
$image->fixOrientation();

```

## Display and save images

```php

/* Display image. It sends correct http-headers and send content of image to browser */
$image->display();

/* Save to file */
$image->save('path/to/dest.png');

/* Save to file and set quality 0-100 (compression rate 1-9 for PNG) */
$image->save('path/to/dest.png', 9);

/* Save in format */
$image->saveAs(IMAGETYPE_WEBP, 'path/to/dest.webp');

/* Send HTTP Content-Type header based on current image type of $image object */
$image->header();

```

# Image Server

```php

$server = new ImageServer();

/* Enable cache */
$server->enableCache($cacheDir);

/* Create image from file */
$server->fromFile($imgFile);

/** Crop/Resize image */
if ($width && $height) {
    $server->resize($width, $height, Resize::ACTION_CROP);
}

/** Display image in requested format */
if ($imagetype) {
  $server->saveAs($imagetype, null, $quality);
  exit;
}

$headers = getallheaders();
$supportsWebp = strpos($headers['Accept'], 'image/webp') !== false;

/** If client supports WEBP, display in WEBP format */
if ($supportsWebp) {
  $server->saveAs(IMAGETYPE_WEBP, null, $quality);
  exit;
}

/** By default display image in source format */
$server->save();
exit;

```