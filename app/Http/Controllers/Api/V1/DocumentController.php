<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ExhibitionResource;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DocumentController extends Controller
{
    public function UpdateInfo()
    {
        $validator = Validator::make(request()->all(), [
            'doc_id' => ['required', 'numeric', 'exists:documents,id'],
            'title' => ['required', 'string', 'max:255', 'min:10'],
            'description' => ['required', 'max:60000'],
        ]);

        if ($validator->fails()) {
            return response([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $doc = Document::find(request()->doc_id);
        $doc->title = request()->title;
        $doc->description = request()->description;
        $doc->touch();
        $doc->save();

        return response([
            'status' => 'success',
            'message' => 'Document has been updated successfully.',
        ], 200);
    }
    public function DownloadDocument()
    {
        $doc = Document::find(request()->document_id);
        if (!$doc)
            return response([
                'status' => 'failed',
                'error' => 'Document not found.',
            ], 404);
        $path = storage_path('app/' . $doc->path);
        return response()->file($path);
    }

    public function GetDocument()
    {
        $doc = Document::find(request()->document_id);

        if (!$doc)
            return response([
                'status' => 'failed',
                'error' => 'Document not found.',
            ], 404);

        return response([
            'status' => 'success',
            'message' => 'Document has been fetched successfully.',
            'data' => [
                'id' => $doc->id,
                'title' => $doc->title,
                'description' => $doc->description,
                'type' => str_contains($doc->commentable->getMorphClass(), 'Exhibition') ? 'Exhibition' : 'Company',
                'for' => str_contains($doc->commentable->getMorphClass(), 'Exhibition') ?
                    ExhibitionResource::make($doc->commentable->getModel()) :
                    CompanyResource::make($doc->commentable->getModel()),
            ]
        ], 200);
    }

}
