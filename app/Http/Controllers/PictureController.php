<?php

namespace App\Http\Controllers;

use App\Models\Picture;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;

class PictureController extends Controller
{
    static public function ResizeAndDecode($image)
    {
        $manager = ImageManager::gd();
        $image = $manager->read($image);
        $image->scaleDown(1280, 720);
        $image->toWebp(75);
        return $image;
    }
    static public function Create($model, $path, $type)
    {
        return $model->pictures()->create([
            'path' => $path,
            'type' => PictureController::Types()[$type],
        ]);
    }
    static private function Types()
    {
        return [
            'profile',
            'company-profile',
            'exhibition-profile',
            'product',
            'post',
            'ticket-side',
            'ticket-main',
        ];
    }
}
