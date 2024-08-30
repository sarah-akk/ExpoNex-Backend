<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PictureController;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    // public function Create()
    // {
    //     $user = request()->user();

    //     $validator = Validator::make(request()->all(), [
    //         'title' => ['required', 'string', 'max:255'],
    //         'body' => ['required', 'string', 'max:4096'],
    //         'images' => ['nullable', 'array', 'min:1'],
    //         'images.*' => ['required', 'mimes:jpg,png,svg,webp', 'max:1024']
    //     ]);

    //     if ($validator->fails()) {
    //         return response([
    //             'status' => 'failed',
    //             'errors' => $validator->errors()
    //         ], 400);
    //     }

    //     $atters = [
    //         'user_id' => $user->id,
    //         'title' => request()->title,
    //         'body' => request()->body
    //     ];

    //     $post = Post::create($atters);

    //     if (request()->file('images'))
    //         foreach (request()->file('images') as $key => $value) {
    //             $path = 'storage/' . $user->company->companyname . '/' . $post->id . $key . '.webp';
    //             $image = PictureController::ResizeAndDecode($value);
    //             $image->save($path);
    //             $pic = PictureController::Create($user->id, $path, 3);
    //             $post->images()->attach($pic->id);
    //         }

    //     //TODO notification email

    //     return response([
    //         'status' => 'success',
    //         'message' => 'Post has been published successfully.',
    //     ], 200);
    // }
}
