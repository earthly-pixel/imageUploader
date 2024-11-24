<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoCollection;
use App\Models\Photo;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ApiController extends BaseController
{
    public function upload(Request $request)
    {
        if($request->hasFile('file')) {

            $uuid = Str::uuid();

            //get filename with extension
            $filenamewithextension = $request->file('file')->getClientOriginalName();
        
            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
        
            //get file extension
            $extension = $request->file('file')->getClientOriginalExtension();

            if($extension == 'mp4')
            {
                $filePath = $request->file('file')->storeAs('/videos', $uuid.'.'.$extension, 'public');

                Video::create([
                    'uuid' => $uuid,
                    'name' => $filename,
                    'file' => '/videos/'.$uuid.'.'.$extension,
                ]);

                return $this->sendSuccess([
                    'file' => url('/storage/videos/'.$uuid.'.'.$extension),
                ], 'Upload Success', 201);
            }

            $filePath = $request->file('file')->storeAs('/uploads', $uuid.'.'.$extension, 'public');

            $image = Image::read($request->file('file'));

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

        return $this->sendError([], 'File Not Valid');
    }

    public function getAllPhoto()
    {
        $photo = Photo::select('id', 'file', 'thumb')->get();

        $data = PhotoCollection::collection($photo);

        return $this->sendSuccess($data, 'Success Get All Image');
    }

    public function getAllVideo()
    {
        $photo = Video::select('id', 'file')->get();

        $data = PhotoCollection::collection($photo);

        return $this->sendSuccess($data, 'Success Get All Image');
    }
}
