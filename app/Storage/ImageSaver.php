<?php

namespace App\Storage;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Alignment;
use Intervention\Image\Color;
use Intervention\Image\Format;
use Intervention\Image\Fraction;

class ImageSaver
{
    public function imageSaver($file)
    {
        // create image manager instance using the preferred driver
        $manager = ImageManager::usingDriver(GdDriver::class);

// read image data from path
        $image = $manager->decodePath($file);

// scale image by height
        $image->scale(height: 100);

// insert a watermark
      //  $image->insert('images/watermark.png', alignment: Alignment::BOTTOM_RIGHT);

// encode edited image
        $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 65);

// save encoded image
        $image->save($file);
    }
}
