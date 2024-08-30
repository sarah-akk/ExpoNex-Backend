<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PictureController;
use App\Http\Resources\CompanyDetailsResource;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function Create()
    {
        $user = request()->user();

        if ($user->role_id == 2) {
            return response([
                'status' => 'failed',
                'errors' => 'sorry you can have only one company.'
            ], 400);
        }
        //TODO document
        $validator = Validator::make(request()->all(), [
            'name' => ['required', 'string', 'max:127'],
            'companyname' => ['required', 'string', 'max:127', 'alpha_dash:ascii', 'unique:companies'],
            'description' => ['required', 'string', 'max:511'],
            'profile_picture' => ['required', 'mimes:jpg,png,svg,webp', 'max:2048'],
            'documents' => ['nullable', 'array', 'min:1'],
            'documents.*' => ['required', 'mimes:jpg,png,svg,webp,pdf,doc,txt,docx', 'max:10240']
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }


        $atters = [
            'user_id' => $user->id,
            'name' => request()->name,
            'companyname' => strtolower(request()->companyname),
            'description' => request()->description
        ];


        $image = PictureController::ResizeAndDecode(request()->file('profile_picture'));
        Storage::makeDirectory('public/companies/' . $atters['companyname']);
        $path = 'storage/companies/' . $atters['companyname'] . '/profilePicture.webp';
        \chmod(public_path('storage/companies/' . $atters['companyname']), 0755);
        $image->save(public_path($path));

        $company = Company::create($atters);

        PictureController::Create($company, $path, 1);

        Storage::makeDirectory('documents/company/' . $atters['companyname']);

        if (request()->file('documents'))
            foreach (request()->file('documents') as $key => $value) {
                $path = $value->store('documents/company/' . $atters['companyname']);
                $company->docs()->create([
                    'path' => $path,
                ]);
            }

        $user->role_id = 2;
        $user->touch();
        $user->save();
        //TODO notification email

        return response([
            'status' => 'success',
            'message' => 'Your company has been created successfully, please wait us to reach out, thanks.',
        ], 200);
    }

    public function GetCompanies()
    {
        $data = CompanyResource::collection(
            Company::
                where('is_approval', 1)
                ->paginate(40)
        )->response()->getData();
        return response([
            'status' => 'success',
            'message' => 'Companies have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function GetCompany()
    {
        $company = Company::
            where('id', request()->company_id)->
            where('is_approval', 1)->
            first();

        if (!$company)
            return response([
                'status' => 'failed',
                'error' => 'Company not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Company details has been fetched successfully.',
            'data' => CompanyDetailsResource::make($company)
        ], 200);
    }









    // Admin

    public function AdminGetCompanies()
    {
        $data = CompanyResource::collection(
            Company::
                paginate(40)
        )->response()->getData();
        return response([
            'status' => 'success',
            'message' => 'Companies have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function AdminGetCompany()
    {
        $company = Company::find(request()->company_id);
        if (!$company)
            return response([
                'status' => 'failed',
                'error' => 'Company not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Company details has been fetched successfully.',
            'data' => CompanyDetailsResource::make($company)
        ], 200);
    }
    public function AdminGetPendingCompanies()
    {
        $data = CompanyResource::collection(
            Company::
                where('is_approval', null)->
                orWhere('is_approval', 0)->
                paginate(40)
        )->response()->getData();

        return response([
            'status' => 'success',
            'message' => 'Companies have been fetched successfully.',
            'data' => $data->data,
            'links' => $data->links,
            'meta' => $data->meta,
        ], 200);
    }
    public function ChangeState()
    {
        $validator = Validator::make(request()->all(), [
            'company_id' => ['required', 'numeric', 'exists:companies,id'],
            'verified' => ['required', 'boolean'],
            'approval' => ['sometimes', 'boolean'],
            'pending' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $company = Company::find(request()->company_id);
        $company->is_approval = request()->approval ?? $company->is_approval;
        $company->is_pending = request()->pending ?? 0;
        $company->is_verified = request()->verified;
        $company->touch();
        $company->save();

        return response([
            'status' => 'success',
            'message' => 'Company has been updated successfully.',
        ], 200);
    }
}
