<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotoCollection;
use App\Http\Resources\VideoCollection;
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

            $tag = $request->tag ? $request->tag : 'no_tag';

            if($extension == 'mp4')
            {
                $filePath = $request->file('file')->storeAs('/videos/'.$tag, $uuid.'.'.$extension, 'public');

                Video::create([
                    'uuid' => $uuid,
                    'name' => $filename,
                    'tag'  => $request->tag,
                    'file' => '/videos/'.$tag.'/'.$uuid.'.'.$extension,
                ]);

                return $this->sendSuccess([
                    'tag'  => $request->tag,
                    'file' => url('/storage/videos/'.$tag.'/'.$uuid.'.'.$extension),
                ], 'Upload Success', 201);
            }

            $filePath = $request->file('file')->storeAs('/uploads/'.$tag, $uuid.'.'.$extension, 'public');

            $image = Image::read($request->file('file'));

            $image->scaleDown(width: 200);

            File::ensureDirectoryExists(storage_path('app/public/thumbnail/'.$tag.'/'));

            $image->toJpeg()->save(storage_path('app/public/thumbnail/').$tag.'/'.$uuid.'.jpg');

            Photo::create([
                'uuid' => $uuid,
                'name' => $filename,
                'tag'  => $request->tag,
                'file' => '/uploads/'.$tag.'/'.$uuid.'.'.$extension,
                'thumb'=> '/thumbnail/'.$tag.'/'.$uuid.'.jpg',
            ]);

            return $this->sendSuccess([
                'tag'  => $request->tag,
                'file' => url('/storage/uploads/'.$tag.'/'.$uuid.'.'.$extension),
                'thumb'=> url('/storage/thumbnail/'.$tag.'/'.$uuid.'.'.$extension),
            ], 'Upload Success', 201);
        }

        return $this->sendError([], 'File Not Valid');
    }

    public function getAllPhoto()
    {
        $photo = Photo::select('id', 'file', 'thumb', 'tag')->get();

        $data = PhotoCollection::collection($photo);

        return $this->sendSuccess($data, 'Success Get All Image');
    }

    public function getAllVideo()
    {
        $video = Video::select('id', 'file', 'tag')->get();

        $data = VideoCollection::collection($video);

        return $this->sendSuccess($data, 'Success Get All Video');
    }
}
