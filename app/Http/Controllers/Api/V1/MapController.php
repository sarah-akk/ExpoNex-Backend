<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Exhibition;
use App\Models\Map;
use App\Models\Section;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class MapController extends Controller
{
    public function CreateAuction()
    {
        $user = request()->user();

        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'string', 'numeric', 'exists:sections,id'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $section = Section::find(request()->section_id);

        if (request()->price < $section->price)
            return response([
                'status' => 'failed',
                'error' => 'Price is under section\'s price.',
            ], 400);

        if ($section->company_id)
            return response([
                'status' => 'failed',
                'error' => 'Section is already taken.',
            ], 400);


        if ($section->map->exhibition->status !== 'pending')
            return response([
                'status' => 'failed',
                'error' => 'Can not register auction in non pending exhibition.',
            ], 400);

        $section->auctions()->attach($user->company->id, ['price' => request()->price]);

        return response([
            'status' => 'success',
            'message' => 'Auction has been registered successfully.',
        ], 200);
    }
    public function GetSectionAuction()
    {
        $section = Section::find(request()->section_id);

        if (!$section)
            return response([
                'status' => 'failed',
                'error' => 'Section not found.',
            ], 404);

        if ($section->company_id === request()->user()->company->id)
            return response([
                'status' => 'success',
                'message' => 'Auction has been fetched successfully.',
            ], 200);

        if ($section->company_id)
            return response([
                'status' => 'failed',
                'error' => 'Section is already taken.',
            ], 400);

        return response([
            'status' => 'success',
            'message' => 'Auction has been fetched successfully.',
            'data' => [
                'prices' => $section->auctions()->orderBy('price', 'desc')->get()->pluck('pivot.price')
            ],
        ], 200);
    }
    public function GetMySectionAuction()
    {
        $user = request()->user();

        $expo = Exhibition::find(request()->exhibition_id);

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);


        $data = $user->company->auctions()->whereIn('section_id', $expo->mapManager->sections->pluck('id')->toArray())->get();
        $data = $data->map(function ($section) {
            return [
                'id' => $section->id,
                'type' => $section->type,
                'positions' => $section->positions,
                'size' => $section->size,
                'price' => $section->price,
                'given_price' => $section->pivot->price,
                'created_at' => Carbon::parse($section->pivot->created_at)->format('Y-m-d H:i:s')
            ];
        });

        return response([
            'status' => 'success',
            'message' => 'Auction has been fetched successfully.',
            'data' => $data
        ], 200);
    }
    public function GetAllMySectionAuction()
    {
        $user = request()->user();

        $data = $user->company->auctions;
        $data = $data->map(function ($section) {
            $expo = Exhibition::find($section->map->exhibition_id);
            return [
                'id' => $section->id,
                'type' => $section->type,
                'positions' => $section->positions,
                'size' => $section->size,
                'price' => $section->price,
                'given_price' => $section->pivot->price,
                'exhibition' => [
                    'id' => $expo->id,
                    'name' => $expo->name
                ],
                'created_at' => Carbon::parse($section->pivot->created_at)->format('Y-m-d H:i:s')
            ];
        });

        return response([
            'status' => 'success',
            'message' => 'Auction has been fetched successfully.',
            'data' => $data
        ], 200);
    }
    public static function Create($map, $block_size, $width, $height, $expo_id)
    {
        $mapModel = Map::create([
            'exhibition_id' => $expo_id,
            'map' => $map,
            'block_size' => $block_size,
            'width' => $width,
            'height' => $height
        ]);

        $map = json_decode($map, false);

        foreach ($map->data as $key => $value) {
            Section::create([
                'map_id' => $mapModel->id,
                'type' => $value->type,
                'positions' => serialize($value->positions),
                'size' => count($value->positions) * $block_size,
                'price' => $value->price
            ]);
        }
        try {
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    //ADMIN

    public function GetCompanyAuction()
    {
        $section = Section::find(request()->section_id);

        if (!$section)
            return response([
                'status' => 'failed',
                'error' => 'Section not found.',
            ], 404);

        $data = $section->auctions->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'given_price' => $company->pivot->price
            ];
        })->toArray();

        return response([
            'status' => 'success',
            'message' => 'Auctions have been fetched successfully.',
            'data' => $data
        ], 200);
    }
    public function Select()
    {
        $validator = Validator::make(request()->all(), [
            'section_id' => ['required', 'string', 'numeric', 'exists:sections,id'],
            'company_id' => ['required', 'string', 'numeric', 'exists:companies,id'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }
        $section = Section::find(request()->section_id);

        if ($section->company_id)
            return response([
                'status' => 'failed',
                'error' => 'Section is already taken, can not edit it.',
            ], 400);

        $section->company_id = request()->company_id;
        $section->touch();
        $section->save();

        return response([
            'status' => 'success',
            'message' => 'Section has been updated successfully.',
        ], 200);
    }
}
