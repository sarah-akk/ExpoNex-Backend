<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PictureController;
use App\Http\Resources\ProductDetailsResource;
use App\Http\Resources\ProductResource;
use App\Models\Exhibition;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function GetProduct()
    {
        $pro = Product::find(request()->product_id);

        if (!$pro)
            return response([
                'status' => 'failed',
                'error' => 'Product not found.',
            ], 404);

        $expo = Exhibition::find($pro->exhibition_id);

        if (strtotime($expo->start_at) > strtotime(Carbon::now()) || $expo->status === 'canceled')
            return response([
                'status' => 'failed',
                'error' => 'Can not show products.',
            ], 403);

        return response([
            'status' => 'success',
            'message' => 'Products have been fetched successfully.',
            'data' => ProductDetailsResource::make($pro)
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

        $data = ProductResource::collection(
            Product::
                where('exhibition_id', request()->exhibition_id)->
                filter()->
                paginate(40)->
                withQueryString()
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Products have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    //Company Owner
    public function Create()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'exhibition_id' => ['required', 'numeric', 'exists:exhibitions,id'],
            'name' => ['required', 'string', 'max:127'],
            'description' => ['required', 'string', 'max:511'],
            'pictures' => ['required', 'array', 'min:1'],
            'pictures.*' => ['required', 'mimes:jpg,png,svg,webp', 'max:2048'],
            'price' => ['required', 'numeric', 'min:500'],
            'quantity' => ['required', 'numeric', 'min:5'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'numeric', 'exists:categories,id'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $expo = Exhibition::find(request()->exhibition_id);
        $check = $expo->mapManager->sections()->where('company_id', $user->company->id)->first();
        if (!$check)
            return response([
                'status' => 'failed',
                'errors' => 'Can not create products in non participation exhibitions.'
            ], 400);

        $pro = Product::create([
            'exhibition_id' => request()->exhibition_id,
            'company_id' => $user->company->id,
            'name' => request()->name,
            'description' => request()->description,
            'price' => request()->price,
            'quantity' => request()->quantity,
        ]);

        foreach (request()->file('pictures') as $key => $value) {
            $image = PictureController::ResizeAndDecode($value);
            Storage::makeDirectory(
                'public/companies/' .
                $user->company->companyname .
                '/products' .
                '/' . request()->exhibition_id .
                '/' . $pro->id
            );
            $path = 'storage/companies/' .
                $user->company->companyname .
                '/products' .
                '/' . request()->exhibition_id .
                '/' . $pro->id .
                '/' . $key . '.webp';
            $image->save(public_path($path));
            PictureController::Create($pro, $path, 3);
            \chmod(public_path(
                'storage/companies/' .
                $user->company->companyname .
                '/products' .
                '/' . request()->exhibition_id .
                '/' . $pro->id
            ), 0755);
        }

        $pro->categories()->attach(request()->categories);

        return response([
            'status' => 'success',
            'message' => 'Product has been created successfully.',
        ], 200);
    }

    public function Update()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'product_id' => ['required', 'numeric', 'exists:products,id'],
            'name' => ['sometimes', 'string', 'max:127'],
            'description' => ['sometimes', 'string', 'max:511'],
            'pictures' => ['sometimes', 'array', 'min:1'],
            'pictures.*' => ['sometimes', 'mimes:jpg,png,svg,webp', 'max:2048'],
            'price' => ['sometimes', 'numeric', 'min:500'],
            'quantity' => ['sometimes', 'numeric', 'min:5'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['required', 'numeric', 'exists:categories,id'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $pro = Product::find(request()->product_id);

        if (request()->pictures) {
            Storage::deleteDirectory(public_path('storage/companies/' .
                $user->company->companyname .
                '/products' .
                '/' . $pro->exhibition_id .
                '/' . $pro->id));
            foreach (request()->file('pictures') as $key => $value) {
                $image = PictureController::ResizeAndDecode($value);
                Storage::makeDirectory(
                    'public/companies/' .
                    $user->company->companyname .
                    '/products' .
                    '/' . $pro->exhibition_id .
                    '/' . $pro->id
                );
                $path = 'storage/companies/' .
                    $user->company->companyname .
                    '/products' .
                    '/' . $pro->exhibition_id .
                    '/' . $pro->id .
                    '/' . $key . '.webp';
                $image->save(public_path($path));
                PictureController::Create($pro, $path, 3);
                \chmod(public_path(
                    'storage/companies/' .
                    $user->company->companyname .
                    '/products' .
                    '/' . $pro->exhibition_id .
                    '/' . $pro->id
                ), 0755);
            }
        }

        $pro->name = request()->name ?? $pro->name;
        $pro->description = request()->description ?? $pro->description;
        $pro->price = request()->price ?? $pro->price;
        $pro->quantity = request()->quantity ?? $pro->quantity;

        $pro->categories()->detach();
        $pro->categories()->attach(request()->categories);

        $pro->touch();
        $pro->save();

        return response([
            'status' => 'success',
            'message' => 'Product has been updated successfully.',
        ], 200);
    }

    public function GetMyProducts()
    {
        $user = request()->user();

        $data = ProductResource::collection(
            $user->company->
                products()->
                filter()->
                paginate(40)->
                withQueryString()
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Products have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }

    public function GetMyProduct()
    {
        $pro = Product::find(request()->product_id);

        if (!$pro)
            return response([
                'status' => 'failed',
                'error' => 'Product not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Products have been fetched successfully.',
            'data' => ProductDetailsResource::make($pro)
        ], 200);
    }

    public function AdminGetProduct()
    {
        $pro = Product::find(request()->product_id);

        if (!$pro)
            return response([
                'status' => 'failed',
                'error' => 'Product not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Products have been fetched successfully.',
            'data' => ProductDetailsResource::make($pro)
        ], 200);
    }
    public function AdminGetProducts()
    {
        $data = ProductResource::collection(
            Product::
                filter()->
                paginate(40)->
                withQueryString()
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Products have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function Delete()
    {
        $pro = Product::find(request()->product_id);

        if (!$pro)
            return response([
                'status' => 'failed',
                'error' => 'Product not found.',
            ], 404);

        $pro->delete();

        return response([
            'status' => 'success',
            'message' => 'Product has been deleted successfully.',
        ], 200);
    }
}
