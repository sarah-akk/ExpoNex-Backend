<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\Wallet;
use App\Models\Company;
use App\Models\Product;
use App\Models\Section;
use App\Models\Category;
use App\Models\Exhibition;
use App\Models\Permission;
use App\Models\TicketItems;
use App\Models\Transaction;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Api\V1\MapController;
use App\Http\Controllers\Api\V1\TicketController;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [];
        for ($i = 1; $i <= 50; $i++) {
            $serial_number = random_int(0, 9) . bin2hex(random_bytes(9)) . random_int(0, 9);
            $temp = $i . '#~#' . $serial_number;
            array_push($coupons, $temp);
            Coupon::create([
                'serial_number' => $serial_number,
                'amount' => 50000
            ]);
        }
        file_put_contents('coupons.txt', implode("\n", $coupons));
        $role = Role::create([
            'name' => 'admin',
            'description' => 'Its a superadmin account'
        ]);
        Role::create([
            'name' => 'company_owner',
            'description' => 'A company Owner'
        ]);
        Role::create([
            'name' => 'exhibition_owner',
            'description' => 'An exhibition Owner'
        ]);

        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            if ($route->getName()) {
                if (str_starts_with($route->getName(), 'ignition'))
                    continue;
                if (str_starts_with($route->getName(), 'passport'))
                    continue;
                if (str_starts_with($route->getName(), 'login'))
                    continue;
                $per = Permission::create([
                    'api_name' => $route->getName(),
                    'description' => 'Dummy data',
                ]);
                $role->permissions()->attach($per->id);
            }
        }

        $admin = User::create([
            'role_id' => 1,
            'name' => 'Test User',
            'email' => 'admin@exponex.com',
            'username' => 'omran',
            'channel_id' => '00000XXXXX00000',
            'phone_number' => '0933373467',
            'password' => '123456789',
            'is_verified' => 1
        ]);
        Wallet::create([
            'user_id' => $admin->id,
            'balance' => 0,
        ]);

        $owner = User::create([
            'role_id' => 2,
            'name' => 'Test Owner',
            'email' => 'owner@exponex.com',
            'username' => 'owner',
            'channel_id' => '00000XXXXX00001',
            'phone_number' => null,
            'password' => '123456789',
            'is_verified' => 1
        ]);
        Wallet::create([
            'user_id' => $owner->id,
            'balance' => 0,
        ]);

        $investor = User::create([
            'role_id' => 3,
            'name' => 'Test User',
            'email' => 'investor@exponex.com',
            'username' => 'investor',
            'channel_id' => '00000XXXXX00002',
            'phone_number' => null,
            'password' => '123456789',
            'is_verified' => 1
        ]);
        Wallet::create([
            'user_id' => $investor->id,
            'balance' => 0,
        ]);

        $omar = User::create([
            'role_id' => 3,
            'name' => 'Omar Hawary',
            'email' => 'omar@exponex.com',
            'username' => 'omar',
            'channel_id' => '00000XXXXX00005',
            'phone_number' => '0995654183',
            'password' => '123456789',
            'is_verified' => 1
        ]);
        Wallet::create([
            'user_id' => $omar->id,
            'balance' => 50000,
        ]);

        $users = User::factory(10)->create();

        $users[1]->role_id = 2;
        $users[1]->save();
        $users[3]->role_id = 3;
        $users[3]->save();
        $users[8]->role_id = 3;
        $users[8]->save();
        $users[9]->role_id = 2;
        $users[9]->save();

//        $c1 = Company::create([
//            'user_id' => $users[1]->id,
//            'name' => fake()->company(),
//            'companyname' => fake()->unique()->word(),
//            'description' => fake()->text(),
//        ]);
//        Storage::makeDirectory('public/companies/' . $c1->companyname);
//        \chmod(public_path('storage/companies/' . $c1->companyname), 0755);
//        $c1->pictures()->create([
//            'type' => 'company-profile',
//            'path' => 'storage/companies/' . $c1->companyname . '/' . fake()->image(
//                public_path('storage/companies/' . $c1->companyname),
//                720,
//                480,
//                null,
//                false,
//            )
//        ]);
//
//        $c2 = Company::create([
//            'user_id' => $owner->id,
//            'name' => fake()->company(),
//            'companyname' => fake()->unique()->word(),
//            'description' => fake()->text(),
//            'is_approval' => 1,
//        ]);
//        Storage::makeDirectory('public/companies/' . $c2->companyname);
//        \chmod(public_path('storage/companies/' . $c2->companyname), 0755);
//        $c2->pictures()->create([
//            'type' => 'company-profile',
//            'path' => 'storage/companies/' . $c2->companyname . '/' . fake()->image(
//                public_path('storage/companies/' . $c2->companyname),
//                720,
//                480,
//                null,
//                false,
//            )
//        ]);
//
//        $c3 = Company::create([
//            'user_id' => $users[9]->id,
//            'name' => fake()->company(),
//            'companyname' => fake()->unique()->word(),
//            'description' => fake()->text(),
//            'is_approval' => 1,
//        ]);
//        Storage::makeDirectory('public/companies/' . $c3->companyname);
//        \chmod(public_path('storage/companies/' . $c3->companyname), 0755);
//        $c3->pictures()->create([
//            'type' => 'company-profile',
//            'path' => 'storage/companies/' . $c3->companyname . '/' . fake()->image(
//                public_path('storage/companies/' . $c3->companyname),
//                720,
//                480,
//                null,
//                false,
//            )
//        ]);

        Category::create([
            'name' => 'food',
            'description' => 'Dummy Data'
        ]);
        Category::create([
            'name' => 'tools',
            'description' => 'Dummy Data'
        ]);
        Category::create([
            'name' => 'electronic',
            'description' => 'Dummy Data'
        ]);
        Category::create([
            'name' => 'audio',
            'description' => 'Dummy Data'
        ]);
        Category::create([
            'name' => 'fashion',
            'description' => 'Dummy Data'
        ]);
        for ($i = 0; $i < 15; $i++) {
            Category::create([
                'name' => fake()->word(),
                'description' => fake()->sentence()
            ]);
        }

        $expo1 = Exhibition::create([
            'owner_id' => $users[8]->id,
            'name' => 'ComicCON',
            'start_at' => fake()->dateTimeBetween('-4 months', '-3 months'),
            'end_at' => fake()->dateTimeBetween('-2 months', '-1 months'),
            'location' => fake()->locale(),
            'coordinates' => json_encode(fake()->localCoordinates()),
            'size' => fake()->numberBetween(500, 1000),
            'description' => fake()->sentence(),
            'status' => 'completed'
        ]);
        Storage::makeDirectory('documents/exhibition/' . $expo1->name);
        $expo1->docs()->create([
            'path' => '/documents/exhibition/' . $expo1->name . '/' . fake()->image(
                storage_path('app/documents/exhibition/' . $expo1->name),
                720,
                480,
                null,
                false,
            )
        ]);
//        Storage::makeDirectory('public/exhibition/' . $expo1->name);
//        \chmod(public_path('storage/exhibition/' . $expo1->name), 0755);
//        $expo1->pictures()->create([
//            'path' => 'storage/exhibition/' . $expo1->name . '/' . fake()->image(
//                public_path('storage/exhibition/' . $expo1->name),
//                720,
//                480,
//                null,
//                false,
//            ),
//            'type' => 'exhibition-profile'
//        ]);
        MapController::Create(
            "{\n    \"data\": [\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                1,\n                2,\n                8\n            ],\n            \"price\": 155000\n        },\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                6,\n                7,\n                14\n            ],\n            \"price\": 15000\n        },\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                11,\n                17,\n                18,\n                19\n            ],\n            \"price\": 205000\n        }\n    ]\n}",
            2,
            7,
            4,
            $expo1->id
        );
        TicketController::Create([
            'exhibition_id' => $expo1->id,
            'ticket_in_place' => 200,
            'ticket_in_place_price' => 1500,
            'ticket_in_virtual_price' => 1000,
            'ticket_prime' => 150,
            'ticket_prime_price' => 2000,
            'ticket_barcode' => fake()->randomNumber(8, true),
            'ticket_title' => 'Awesome Ticket',
            'ticket_description' => fake()->sentence(),
            'ticket_side_type' => 'color',
            'ticket_main_type' => 'color',
            'ticket_side_style' => fake()->hexColor(),
            'ticket_main_style' => fake()->hexColor(),
        ]);

        $expo2 = Exhibition::create([
            'owner_id' => $users[8]->id,
            'name' => 'WorkCam',
            'start_at' => fake()->dateTimeBetween('-2 days', 'now'),
            'end_at' => fake()->dateTimeBetween('+2 months', '3 months'),
            'location' => fake()->locale(),
            'coordinates' => json_encode(fake()->localCoordinates()),
            'size' => fake()->numberBetween(500, 1000),
            'description' => fake()->sentence(),
            'status' => 'active'
        ]);
        Storage::makeDirectory('documents/exhibition/' . $expo2->name);
        $expo2->docs()->create([
            'path' => '/documents/exhibition/' . $expo2->name . '/' . fake()->image(
                storage_path('app/documents/exhibition/' . $expo2->name),
                720,
                480,
                null,
                false,
            )
        ]);
//        Storage::makeDirectory('public/exhibition/' . $expo2->name);
//        \chmod(public_path('storage/exhibition/' . $expo2->name), 0755);
//        $expo2->pictures()->create([
//            'path' => 'storage/exhibition/' . $expo2->name . '/' . fake()->image(
//                public_path('storage/exhibition/' . $expo2->name),
//                720,
//                480,
//                null,
//                false,
//            ),
//            'type' => 'exhibition-profile'
//        ]);
        MapController::Create(
            "{\n    \"data\": [\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                1,\n                2,\n                8\n            ],\n            \"price\": 155000\n        },\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                6,\n                7,\n                14\n            ],\n            \"price\": 15000\n        },\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                11,\n                17,\n                18,\n                19\n            ],\n            \"price\": 205000\n        }\n    ]\n}",
            2,
            7,
            8,
            $expo2->id
        );
        TicketController::Create([
            'exhibition_id' => $expo2->id,
            'ticket_in_place' => 200,
            'ticket_in_place_price' => 1500,
            'ticket_in_virtual_price' => 1000,
            'ticket_prime' => 150,
            'ticket_prime_price' => 2000,
            'ticket_barcode' => fake()->randomNumber(8, true),
            'ticket_title' => 'Awesome Ticket',
            'ticket_description' => fake()->sentence(),
            'ticket_side_type' => 'color',
            'ticket_main_type' => 'color',
            'ticket_side_style' => fake()->hexColor(),
            'ticket_main_style' => fake()->hexColor(),
        ]);


        $expo3 = Exhibition::create([
            'owner_id' => $users[8]->id,
            'name' => 'Back to school',
            'start_at' => fake()->dateTimeBetween('+4 months', '+5 months'),
            'end_at' => fake()->dateTimeBetween('+6 months', '+7 months'),
            'location' => fake()->locale(),
            'coordinates' => json_encode(fake()->localCoordinates()),
            'size' => fake()->numberBetween(500, 1000),
            'description' => fake()->sentence(),
            'status' => 'pending'
        ]);
        Storage::makeDirectory('documents/exhibition/' . $expo3->name);
        $expo3->docs()->create([
            'path' => '/documents/exhibition/' . $expo3->name . '/' . fake()->image(
                storage_path('app/documents/exhibition/' . $expo3->name),
                720,
                480,
                null,
                false,
            )
        ]);
//        Storage::makeDirectory('public/exhibition/' . $expo3->name);
//        \chmod(public_path('storage/exhibition/' . $expo3->name), 0755);
//        $expo3->pictures()->create([
//            'path' => 'storage/exhibition/' . $expo3->name . '/' . fake()->image(
//                public_path('storage/exhibition/' . $expo3->name),
//                720,
//                480,
//                null,
//                false,
//            ),
//            'type' => 'exhibition-profile'
//        ]);
        MapController::Create(
            "{\n    \"data\": [\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                1,\n                2,\n                8\n            ],\n            \"price\": 155000\n        },\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                6,\n                7,\n                14\n            ],\n            \"price\": 15000\n        },\n        {\n            \"type\": \"S\",\n            \"positions\": [\n                11,\n                17,\n                18,\n                19\n            ],\n            \"price\": 205000\n        }\n    ]\n}",
            2,
            7,
            8,
            $expo3->id
        );
        TicketController::Create([
            'exhibition_id' => $expo3->id,
            'ticket_in_place' => 200,
            'ticket_in_place_price' => 1500,
            'ticket_in_virtual_price' => 1000,
            'ticket_prime' => 150,
            'ticket_prime_price' => 2000,
            'ticket_barcode' => fake()->randomNumber(8, true),
            'ticket_title' => 'Awesome Ticket',
            'ticket_description' => fake()->sentence(),
            'ticket_side_type' => 'color',
            'ticket_main_type' => 'color',
            'ticket_side_style' => fake()->hexColor(),
            'ticket_main_style' => fake()->hexColor(),
        ]);


        for ($i = 0; $i < 10; $i++) {
            $pro = Product::create([
                'exhibition_id' => $expo1->id,
                'name' => fake()->word(),
                'description' => fake()->sentence(),
                'price' => fake()->randomNumber(3, true),
                'quantity' => fake()->randomNumber(2, true),
                'company_id' => $c2->id,
            ]);
            Storage::makeDirectory(
                'public/companies/' .
                $c2->companyname .
                '/products' .
                '/' . $expo1->id .
                '/' . $pro->id
            );
            \chmod(public_path(
                'storage/companies/' .
                $c2->companyname .
                '/products' .
                '/' . $expo1->id .
                '/' . $pro->id
            ), 0755);
            for ($j = 0; $j < 3; $j++) {
                $pro->pictures()->create([
                    'path' => 'storage/companies/' .
                        $c2->companyname .
                        '/products' .
                        '/' . $expo1->id . '/' . $pro->id . '/' . fake()->image(
                                public_path(
                                    'storage/companies/' .
                                    $c2->companyname .
                                    '/products' .
                                    '/' . $expo1->id .
                                    '/' . $pro->id
                                ),
                                720,
                                480,
                                null,
                                false
                            ),
                    'type' => 'product'
                ]);
            }
            $pro->categories()->attach([1, 2, 5, 9]);
        }
        for ($i = 0; $i < 10; $i++) {
            $pro = Product::create([
                'exhibition_id' => $expo1->id,
                'name' => fake()->word(),
                'description' => fake()->sentence(),
                'price' => fake()->randomNumber(3, true),
                'quantity' => fake()->randomNumber(2, true),
                'company_id' => $c3->id,
            ]);
            Storage::makeDirectory(
                'public/companies/' .
                $c3->companyname .
                '/products' .
                '/' . $expo1->id .
                '/' . $pro->id
            );
            \chmod(public_path(
                'storage/companies/' .
                $c3->companyname .
                '/products' .
                '/' . $expo1->id .
                '/' . $pro->id
            ), 0755);
            for ($j = 0; $j < 3; $j++) {
                $pro->pictures()->create([
                    'path' => 'storage/companies/' .
                        $c3->companyname .
                        '/products' .
                        '/' . $expo1->id . '/' . $pro->id . '/' . fake()->image(
                                public_path(
                                    'storage/companies/' .
                                    $c3->companyname .
                                    '/products' .
                                    '/' . $expo1->id .
                                    '/' . $pro->id
                                ),
                                720,
                                480,
                                null,
                                false
                            ),
                    'type' => 'product'
                ]);
            }
            $pro->categories()->attach([3, 11, 13, 18]);
        }


        for ($i = 0; $i < 10; $i++) {
            $pro = Product::create([
                'exhibition_id' => $expo2->id,
                'name' => fake()->word(),
                'description' => fake()->sentence(),
                'price' => fake()->randomNumber(3, true),
                'quantity' => fake()->randomNumber(2, true),
                'company_id' => $c2->id,
            ]);
            Storage::makeDirectory(
                'public/companies/' .
                $c2->companyname .
                '/products' .
                '/' . $expo2->id .
                '/' . $pro->id
            );
            \chmod(public_path(
                'storage/companies/' .
                $c2->companyname .
                '/products' .
                '/' . $expo2->id .
                '/' . $pro->id
            ), 0755);
            for ($j = 0; $j < 3; $j++) {
                $pro->pictures()->create([
                    'path' => 'storage/companies/' .
                        $c2->companyname .
                        '/products' .
                        '/' . $expo2->id . '/' . $pro->id . '/' . fake()->image(
                                public_path(
                                    'storage/companies/' .
                                    $c2->companyname .
                                    '/products' .
                                    '/' . $expo2->id .
                                    '/' . $pro->id
                                ),
                                720,
                                480,
                                null,
                                false
                            ),
                    'type' => 'product'
                ]);
            }
            $pro->categories()->attach([1, 2, 5, 9]);
        }
        for ($i = 0; $i < 10; $i++) {
            $pro = Product::create([
                'exhibition_id' => $expo2->id,
                'name' => fake()->word(),
                'description' => fake()->sentence(),
                'price' => fake()->randomNumber(3, true),
                'quantity' => fake()->randomNumber(2, true),
                'company_id' => $c3->id,
            ]);
            Storage::makeDirectory(
                'public/companies/' .
                $c3->companyname .
                '/products' .
                '/' . $expo2->id .
                '/' . $pro->id
            );
            \chmod(public_path(
                'storage/companies/' .
                $c3->companyname .
                '/products' .
                '/' . $expo2->id .
                '/' . $pro->id
            ), 0755);
            for ($j = 0; $j < 3; $j++) {
                $pro->pictures()->create([
                    'path' => 'storage/companies/' .
                        $c3->companyname .
                        '/products' .
                        '/' . $expo2->id . '/' . $pro->id . '/' . fake()->image(
                                public_path(
                                    'storage/companies/' .
                                    $c3->companyname .
                                    '/products' .
                                    '/' . $expo2->id .
                                    '/' . $pro->id
                                ),
                                720,
                                480,
                                null,
                                false
                            ),
                    'type' => 'product'
                ]);
            }
            $pro->categories()->attach([3, 11, 13, 18]);
        }

        $order1 = Order::create([
            'user_id' => $omar->id,
            'order_number' => '#' . bin2hex(random_bytes(4)) . random_int(0, 9) . random_int(0, 9),
            'amount' => 8000,
            'status' => 'cancelled'
        ]);

        Transaction::create([
            'wallet_id' => $omar->wallet->id,
            'order_id' => $order1->id
        ]);

        foreach ([21, 22, 26, 27, 30, 31] as $key => $value) {
            Cart::create([
                'product_id' => $value,
                'order_id' => $order1->id,
                'quantity' => 5
            ]);
        }


        $order2 = Order::create([
            'user_id' => $omar->id,
            'order_number' => '#' . bin2hex(random_bytes(4)) . random_int(0, 9) . random_int(0, 9),
            'amount' => 7800,
            'status' => 'completed'
        ]);

        Transaction::create([
            'wallet_id' => $omar->wallet->id,
            'order_id' => $order2->id
        ]);

        foreach ([21, 27, 30, 31] as $key => $value) {
            Cart::create([
                'product_id' => $value,
                'order_id' => $order2->id,
                'quantity' => 9
            ]);
        }

        $order3 = Order::create([
            'user_id' => $omar->id,
            'order_number' => '#' . bin2hex(random_bytes(4)) . random_int(0, 9) . random_int(0, 9),
            'amount' => 5435,
            'status' => 'completed'
        ]);

        Transaction::create([
            'wallet_id' => $omar->wallet->id,
            'order_id' => $order3->id
        ]);

        foreach ([30, 31] as $key => $value) {
            Cart::create([
                'product_id' => $value,
                'order_id' => $order3->id,
                'quantity' => 2
            ]);
        }


        $order4 = Order::create([
            'user_id' => $omar->id,
            'order_number' => '#' . bin2hex(random_bytes(4)) . random_int(0, 9) . random_int(0, 9),
            'amount' => 12000,
            'status' => 'completed'
        ]);

        Transaction::create([
            'wallet_id' => $omar->wallet->id,
            'order_id' => $order4->id
        ]);

        foreach ([21, 27, 30, 31, 35, 36, 37] as $key => $value) {
            Cart::create([
                'product_id' => $value,
                'order_id' => $order4->id,
                'quantity' => 12
            ]);
        }

        $order = Order::create([
            'user_id' => $omar->id,
            'order_number' => '#' . bin2hex(random_bytes(9)) . random_int(0, 9) . random_int(0, 9),
            'amount' => 3500,
            'status' => 'completed'
        ]);

        TicketItems::create([
            'order_id' => $order->id,
            'ticket_id' => $expo2->ticketManager->id,
            'type' => 'prime',
            'quantity' => 3,
        ]);

        $order = Order::create([
            'user_id' => $omar->id,
            'order_number' => '#' . bin2hex(random_bytes(9)) . random_int(0, 9) . random_int(0, 9),
            'amount' => 1500,
            'status' => 'completed'
        ]);

        TicketItems::create([
            'order_id' => $order->id,
            'ticket_id' => $expo2->ticketManager->id,
            'type' => 'in_place',
            'quantity' => 1,
        ]);

        $order = Order::create([
            'user_id' => $omar->id,
            'order_number' => '#' . bin2hex(random_bytes(9)) . random_int(0, 9) . random_int(0, 9),
            'amount' => 5000,
            'status' => 'completed'
        ]);

        TicketItems::create([
            'order_id' => $order->id,
            'ticket_id' => $expo2->ticketManager->id,
            'type' => 'virtually',
            'quantity' => 5,
        ]);


        Section::find(4)->auctions()->attach($owner->company->id, ['price' => 55000]);
        Section::find(4)->auctions()->attach($users[9]->company->id, ['price' => 75000]);
        Section::find(4)->auctions()->attach($owner->company->id, ['price' => 150000]);
        Section::find(5)->auctions()->attach($owner->company->id, ['price' => 541600]);
        Section::find(6)->auctions()->attach($owner->company->id, ['price' => 350000]);
    }

}
