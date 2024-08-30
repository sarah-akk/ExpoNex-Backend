<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;


use App\Http\Controllers\PictureController;
use App\Http\Resources\ExhibitionoDetailsResource;
use App\Http\Resources\ExhibitionResource;
use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExhibitionController extends Controller
{
    public function GetExhibitions()
    {
        $data = ExhibitionResource::collection(
            Exhibition::
                where(function ($query) {
                    $query->
                        where('status', 'complated')->
                        orWhere('status', 'active');
                })->
                paginate(10)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Exhibitions have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function GetExhibition()
    {
        $expo = Exhibition::
            where(function ($query) {
                $query->
                    where('status', 'complated')->
                    orWhere('status', 'active');
            })->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => ExhibitionoDetailsResource::make($expo),
        ], 200);
    }

    //Admin
    public function Create()
    {
        $validator = Validator::make(request()->all(), [
            'investor_id' => ['required', 'numeric', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'start_at' => ['required', 'date', 'after:tomorrow'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'location' => ['required', 'string', 'max:255'],
            'coordinates' => ['required', 'json'],
            'expo_size' => ['required', 'numeric',],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*' => ['required', 'mimes:jpg,png,svg,webp,pdf,doc,txt,docx', 'max:10240'],
            'description' => ['required', 'string', 'max:1000'],

            'profile_picture' => ['required', 'mimes:jpg,png,svg,webp', 'max:2048'],
            'map' => ['required', 'json'],
            'block_size' => ['required', 'numeric',],
            'width' => ['required', 'numeric',],
            'height' => ['required', 'numeric',],

            'ticket_in_place' => ['required', 'numeric', 'min:100'],
            'ticket_in_place_price' => ['required', 'numeric', 'min:500'],
            'ticket_in_virtual_price' => ['required', 'numeric', 'min:100'],
            'ticket_prime' => ['required', 'numeric', 'min:50'],
            'ticket_prime_price' => ['required', 'numeric', 'min:400'],

            'ticket_barcode' => ['required', 'numeric', 'digits:8'],
            'ticket_title' => ['required', 'string', 'max:127'],
            'ticket_description' => ['required', 'string', 'max:255'],
            'ticket_side_type' => ['required', 'string', 'in:picture,color'],
            'ticket_main_type' => ['required', 'string', 'in:picture,color'],
            'ticket_side_style' => request()->ticket_side_type === 'color' ?
                ['required', 'string', 'hex_color'] :
                ['required', 'mimes:jpg,png,svg,webp', 'max:2048'],
            'ticket_main_style' => request()->ticket_main_type === 'color' ?
                ['required', 'string', 'hex_color'] :
                ['required', 'mimes:jpg,png,svg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $investor = User::find(request()->investor_id);

        if ($investor->role_id !== 3)
            return response([
                'status' => 'failed',
                'errors' => 'Please provide valid investor user.'
            ], 400);

        $atters = [
            'owner_id' => $investor->id,
            'name' => request()->name,
            'start_at' => request()->start_at,
            'end_at' => request()->end_at,
            'location' => request()->location,
            'size' => request()->expo_size,
            'status' => 'pending',
            'description' => request()->description,
            'coordinates' => request()->coordinates,
        ];

        Storage::makeDirectory('documents/exhibition/' . request()->name);

        $expo = Exhibition::create($atters);

        if (request()->file('documents'))
            foreach (request()->file('documents') as $key => $value) {
                $path = $value->store('documents/exhibition/' . request()->name);
                $expo->docs()->create([
                    'path' => $path,
                ]);
            }

        //TODO email notification

        Storage::makeDirectory('public/exhibition/' . $atters['name']);
        $path = 'storage/exhibition/' . $atters['name'] . '/profilePicture.webp';
        \chmod(public_path('storage/exhibition/' . $atters['name']), 0755);
        $image = PictureController::ResizeAndDecode(request()->file('profile_picture'));
        $image->save(public_path($path));
        PictureController::Create($expo, $path, 2);

        MapController::Create(request()->map, request()->block_size, request()->width, request()->height, $expo->id);

        TicketController::Create([
            'expo_name' => $expo->name,
            'exhibition_id' => $expo->id,
            'ticket_in_place' => request()->ticket_in_place,
            'ticket_in_place_price' => request()->ticket_in_place_price,
            'ticket_in_virtual_price' => request()->ticket_in_virtual_price,
            'ticket_prime' => request()->ticket_prime,
            'ticket_prime_price' => request()->ticket_prime_price,
            'ticket_side_type' => request()->ticket_side_type,
            'ticket_main_type' => request()->ticket_main_type,
            'ticket_side_style' => request()->file('ticket_side_style') ?? request()->ticket_side_style,
            'ticket_main_style' => request()->file('ticket_main_style') ?? request()->ticket_main_style,
            'ticket_barcode' => request()->ticket_barcode,
            'ticket_title' => request()->ticket_title,
            'ticket_description' => request()->ticket_description,
        ]);



        return response([
            'status' => 'success',
            'message' => 'Your exhibition has been created successfully, please wait us to reach out, thanks.',
        ], 200);
    }

    public function Update()
    {
        $validator = Validator::make(request()->all(), [
            'exhibition_id' => ['required', 'string', 'numeric', 'exists:exhibitions,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'start_at' => ['sometimes', 'date', 'after:tomorrow'],
            'end_at' => ['sometimes', 'date', 'after:start_at'],
            'location' => ['sometimes', 'string', 'max:255'],
            'expo_size' => ['sometimes', 'numeric',],
            'description' => ['sometimes', 'string', 'max:1000'],
            'profile_picture' => ['nullable', 'mimes:jpg,png,svg,webp', 'max:2048'],
            'coordinates' => ['sometimes', 'json'],

            'ticket_in_place' => ['sometimes', 'numeric', 'min:100'],
            'ticket_in_place_price' => ['sometimes', 'numeric', 'min:500'],
            'ticket_in_virtual_price' => ['sometimes', 'numeric', 'min:100'],
            'ticket_prime' => ['sometimes', 'numeric', 'min:50'],
            'ticket_prime_price' => ['sometimes', 'numeric', 'min:100'],

            'ticket_barcode' => ['sometimes', 'numeric', 'digits:8'],
            'ticket_title' => ['sometimes', 'string', 'max:127'],
            'ticket_description' => ['sometimes', 'string', 'max:255'],
            'ticket_side_type' => ['sometimes', 'string', 'in:picture,color'],
            'ticket_main_type' => ['sometimes', 'string', 'in:picture,color'],
            'ticket_side_style' => request()->ticket_side_type === 'color' ?
                ['required_if:ticket_side_type,color', 'string', 'hex_color'] :
                ['required_if:ticket_side_type,picture', 'mimes:jpg,png,svg,webp', 'max:2048'],
            'ticket_main_style' => request()->ticket_main_type === 'color' ?
                ['required_if:ticket_side_style,color', 'string', 'hex_color'] :
                ['required_if:ticket_side_style,picture', 'mimes:jpg,png,svg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $expo = Exhibition::find(request()->exhibition_id);

        if (strtotime($expo->start_at) <= strtotime(Carbon::now()))
            return response([
                'status' => 'failed',
                'errors' => 'You can not edit an already started exhibition.'
            ], 400);

        if (request()->name) {
            rename(public_path('storage/' . $expo->name), public_path('storage/' . request()->name));
            rename(storage_path('app/documents/exhibition/' . $expo->name), storage_path('app/documents/exhibition/' . request()->name));
        }
        $expo->name = request()->name ?? $expo->name;


        $expo->start_at = request()->start_at ?? $expo->start_at;
        $expo->end_at = request()->end_at ?? $expo->end_a;
        $expo->location = request()->location ?? $expo->location;
        $expo->coordinates = request()->coordinates ?? $expo->coordinates;
        $expo->size = request()->expo_size ?? $expo->size;
        $expo->description = request()->description ?? $expo->description;

        if (request()->file('profile_picture')) {
            $path = 'storage/' . $expo->name . '/profilePicture.webp';
            $image = PictureController::ResizeAndDecode(request()->file('profile_picture'));
            $image->save($path);
        }

        $expo->ticketManager->title = request()->ticket_title ?? $expo->ticketManager->title;
        $expo->ticketManager->description = request()->ticket_description ?? $expo->ticketManager->description;
        $expo->ticketManager->barcode = request()->ticket_barcode ?? $expo->ticketManager->barcode;
        $expo->ticketManager->in_place = request()->ticket_in_place ?? $expo->ticketManager->in_place;
        $expo->ticketManager->in_place_price = request()->ticket_in_place_price ?? $expo->ticketManager->in_place_price;
        $expo->ticketManager->in_virtual_price = request()->ticket_in_virtual_price ?? $expo->ticketManager->in_virtual_price;
        $expo->ticketManager->prime = request()->ticket_prime ?? $expo->ticketManager->prime;
        $expo->ticketManager->prime_price = request()->ticket_prime_price ?? $expo->ticketManager->prime_price;

        if (request()->ticket_side_type === 'color')
            $expo->ticketManager->side_style = request()->ticket_side_style ?? $expo->ticketManager->side_style;
        else if (request()->ticket_main_type)
            TicketController::CreatePicture($expo->name, request()->file('ticket_side_style'), $expo->ticketManager, 'ticketSideStyle.webp');

        if (request()->ticket_main_type === 'color')
            $expo->ticketManager->side_style = request()->ticket_main_style ?? $expo->ticketManager->main_style;
        else if (request()->ticket_main_type)
            TicketController::CreatePicture($expo->name, request()->file('ticket_main_style'), $expo->ticketManager, 'ticketMainStyle.webp');


        $expo->ticketManager->touch();
        $expo->ticketManager->save();

        $expo->touch();
        $expo->save();

        return response([
            'status' => 'success',
            'massage' => 'Exhibition has been updated successfully.'
        ]);
    }

    public function ChangeState()
    {
        $validator = Validator::make(request()->all(), [
            'exhibition_id' => ['required', 'string', 'numeric', 'exists:exhibitions,id'],
            'status' => ['required', 'string', 'in:active,cancelled'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $expo = Exhibition::find(request()->exhibition_id);

        $expo->status = request()->status;
        $expo->touch();
        $expo->save();

        return response([
            'status' => 'success',
            'massage' => 'Exhibition\'s state has been updated successfully.'
        ]);
    }
    public function AdminGetExhibitions()
    {
        $data = ExhibitionResource::collection(
            Exhibition::
                paginate(40)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Exhibitions have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function AdminGetExhibition()
    {
        $expo = Exhibition::find(request()->exhibition_id);

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => ExhibitionoDetailsResource::make($expo),
        ], 200);
    }

    // Owner
    public function GetPendingExhibitions()
    {
        $data = ExhibitionResource::collection(
            Exhibition::
                where('status', 'pending')->
                paginate(10)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Exhibitions have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function GetPendingExhibition()
    {
        $expo = Exhibition::
            where('id', request()->exhibition_id)->
            where('status', 'pending')->first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => ExhibitionoDetailsResource::make($expo),
        ], 200);
    }


    //Ivestor 

    public function GetMyExpos()
    {
        $user = request()->user();

        $data = ExhibitionResource::collection(
            $user->
                exhibitions()->
                paginate(10)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Exhibitions have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function GetMyExpo()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => ExhibitionoDetailsResource::make($expo),
        ], 200);
    }
    public function GetProductsNumber()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        $data = [
            'products_number' => count($expo->products)
        ];

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => $data,
        ], 200);
    }
    public function GetSoldProducts()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        $data = [
            'products_sold' => array_sum(
                $expo->
                    products()->
                    whereHas('cart')->
                    get()->
                    map(function ($product) {
                        return array_sum(
                            $product->cart->pluck('quantity')->toArray()
                        );
                    })->
                    toArray()
            )
        ];

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => $data,
        ], 200);
    }
    public function GetTicketSoldsInPlace()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        $data = [
            'ticket_sold_in_place' => array_sum(
                $expo->ticketManager->
                    ticketItems()->
                    where('type', 'in_place')->
                    get()->
                    pluck('quantity')->
                    toArray()
            )
        ];

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => $data,
        ], 200);
    }
    public function GetTicketSoldVirtually()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        $data = [
            'ticket_sold_virtually' => array_sum(
                $expo->ticketManager->
                    ticketItems()->
                    where('type', 'virtually')->
                    get()->
                    pluck('quantity')->
                    toArray()
            )
        ];

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => $data,
        ], 200);
    }
    public function GetTicketSoldPrime()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        $data = [
            'ticket_sold_prime' => array_sum(
                $expo->ticketManager->
                    ticketItems()->
                    where('type', 'prime')->
                    get()->
                    pluck('quantity')->
                    toArray()
            )
        ];

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => $data,
        ], 200);
    }
    public function GetSectionTaken()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => $expo->mapManager->sections->map(function ($section) {
                return array_merge($section->toArray(), [
                    'auctions' => count($section->auctions) === 0 ? [] : $section->auctions()->orderBy('price', 'desc')->get()->map(function ($auction) {
                        return [
                            'company_id' => $auction->id,
                            'company_name' => $auction->name,
                            'given_price' => $auction->pivot->price
                        ];
                    })
                ]);
            }),
        ], 200);
    }
    public function GetRevenue()
    {
        $user = request()->user();

        $expo = $user->
            exhibitions()->
            where('id', request()->exhibition_id)->
            first();

        if (!$expo)
            return response([
                'status' => 'failed',
                'error' => 'Exhibition not found.',
            ], 404);

        $amount = 0;

        $data1 = array_sum(
            $expo->
                products()->
                whereHas('cart')->
                get()->
                map(function ($product) {
                    return array_sum(
                        $product->cart->map(function ($item) use ($product) {
                            return $item->quantity * $product->price;
                        })->toArray()
                    );
                })->
                toArray()
        );
        $data2 = array_sum(
            $expo->ticketManager->
                ticketItems()->
                where('type', 'in_place')->
                get()->map(function ($ticket) use ($expo) {
                    return $ticket->quantity * $expo->ticketManager->in_place_price;
                })->toArray()
        );
        $data3 = array_sum(
            $expo->ticketManager->
                ticketItems()->
                where('type', 'virtually')->
                get()->
                map(function ($ticket) use ($expo) {
                    return $ticket->quantity * $expo->ticketManager->in_virtual_price;
                })->toArray()
        );
        $data4 = array_sum(
            $expo->ticketManager->
                ticketItems()->
                where('type', 'prime')->
                get()->
                map(function ($ticket) use ($expo) {
                    return $ticket->quantity * $expo->ticketManager->prime_price;
                })->toArray()
        );
        $data5 = array_sum($expo->mapManager->sections->map(function ($section) {
            return count($section->auctions) === 0 ? 0 : $section->auctions()->orderBy('price', 'desc')->first()->pivot->price;
        })->toArray());

        return response([
            'status' => 'success',
            'message' => 'Exhibition has been fetched successfully.',
            'data' => [
                'revenue' => $data1 + $data2 + $data3 + $data4 + $data5
            ],
        ], 200);
    }
}