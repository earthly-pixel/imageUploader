<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoCollection;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ApiController extends BaseController
{
    public function upload(Request $request)
    {
        if($request->hasFile('image')) {

            $uuid = Str::uuid();

            //get filename with extension
            $filenamewithextension = $request->file('image')->getClientOriginalName();
        
            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
        
            //get file extension
            $extension = $request->file('image')->getClientOriginalExtension();

            $filePath = $request->file('image')->storeAs('/uploads', $uuid.'.'.$extension, 'public');

            $image = Image::read($request->file('image'));

            $image->scaleDown(width: 200);

            File::ensureDirectoryExists(storage_path('app/public/thumbnail/'));

            $image->toJpeg()->save(storage_path('app/public/thumbnail/').$uuid.'.jpg');

            Photo::create([
                'uuid' => $uuid,
                'name' => $filename,
                'file' => '/uploads/'.$uuid.'.'.$extension,
                'thumb'=> '/thumbnail/'.$uuid.'.jpg',
            ]);

            return $this->sendSuccess([
                'file' => url('/storage/uploads/'.$uuid.'.'.$extension),
                'thumb'=> url('/storage/thumbnail/'.$uuid.'.'.$extension),
            ], 'Upload Success', 201);
        }

        return $this->sendError([], 'Image Not Valid');
    }

    public function getAll()
    {
        $photo = Photo::select('id', 'file', 'thumb')->get();

        $data = PhotoCollection::collection($photo);

        return $this->sendSuccess($data, 'Success Get All Image');
    }
}
