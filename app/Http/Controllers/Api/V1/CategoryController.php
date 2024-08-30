<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function Get()
    {
        $data = CategoryResource::collection(Category::all());

        return response([
            'status' => 'success',
            'message' => 'Category have been fetched successfully.',
            'data' => $data,
        ], 200);
    }

    public function GetProducts()
    {
        $expo = Exhibition::find(request()->exhibition_id);

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        if (strtotime($expo->start_at) > strtotime(Carbon::now()) || $expo->status === 'canceled')
            return response([
                'status' => 'failed',
                'error' => 'Can not show products.',
            ], 403);

        $cat = Category::find(request()->category_id);

        if (!$cat)
            return response([
                'status' => 'failed',
                'error' => 'Category not found.',
            ], 404);

        $data = ProductResource::collection(
            $cat->
                products()->
                where('exhibition_id', request()->exhibition_id)->
                paginate(40)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Products have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }

    //Admin
    public function Create()
    {
        $validator = Validator::make(request()->all(), [
            'name' => ['required', 'string', 'max:63'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        Category::create([
            'name' => request()->name,
            'description' => request()->description,
        ]);

        return response([
            'status' => 'success',
            'message' => 'Category has been created successfully.'
        ], 200);
    }

    public function Update()
    {
        $validator = Validator::make(request()->all(), [
            'category_id' => ['required', 'numeric', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:63'],
            'description' => ['sometimes', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $cat = Category::find(request()->category_id);

        $cat->name = request()->name ?? $cat->name;
        $cat->description = request()->description ?? $cat->description;

        $cat->touch();
        $cat->save();

        return response([
            'status' => 'success',
            'message' => 'Category has been updated successfully.'
        ], 200);
    }
    public function Delete()
    {
        $validator = Validator::make(request()->all(), [
            'category_id' => ['required', 'numeric', 'exists:categories,id'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $cat = Category::find(request()->category_id);

        $cat->delete();

        return response([
            'status' => 'success',
            'message' => 'Category has been deleted successfully.'
        ], 200);
    }
}
