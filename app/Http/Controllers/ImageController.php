<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;

class ImageController extends Controller
{
    public function crop(Request $request): Response
    {
        $path = $request->query('path');
        $w = (int) $request->query('w', 200);
        $h = (int) $request->query('h', 200);

        abort_unless($path, 400, 'path is required');
        abort_unless(Storage::disk('public')->exists($path), 404);

        $contents = Storage::disk('public')->get($path);

        $manager = ImageManager::usingDriver(GdDriver::class);
        $image = $manager->decodeBinary($contents);
        $image->coverDown($w, $h);

        $encoded = $image->encodeUsingFormat(Format::WEBP, quality: 85);

        return response($encoded->toString(), 200, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
