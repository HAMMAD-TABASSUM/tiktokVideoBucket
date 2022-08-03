<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use App\Jobs\SendEmailJb;
use FFMPEG\FFMPEG;
use Illuminate\Support\Facades\File;
use Google\Cloud\Storage\StorageClient;

class VideoController extends Controller
{
    public function video(Request $request){
        $video=new Video;
        $file=$request->file('file')->store('VideoData');
        // $this->dispatch(new \App\Jobs\SendEmailJb($request))->delay(now()->addSeconds(10));
        $location=base_path(str_replace('/', '\\', "storage/app/".$file));
        $filethumbnail = uniqid() . '.png';
        // $thumbnail=base_path(str_replace('/', '\\', "storage/app/VideoData/".$filethumbnail));
        $filegif = uniqid() . '.gif';
        // $gif=base_path(str_replace('/', '\\', "storage/app/VideoData/".$filegif));
        $fileVideo = uniqid() . '.mp4';
        // $shortVideo=base_path(str_replace('/', '\\', "storage/app/VideoData/".$filevideo));
        shell_exec("ffmpeg -i " . $location . " -vf fps=1/30 " . $filethumbnail);
        shell_exec("ffmpeg -i " . $location . " -ss 00:00:00 -t 00:00:02 " . $filegif);
        shell_exec("ffmpeg -i " . $location . " -ss 00:00:00 -t 00:01:00 " . $fileVideo);
        $storage = new StorageClient([
            'keyFilePath' => base_path('google-service-account.json'),
        ]);

        $bucketName = 'elegant-bonbon-357016';
        $bucket = $storage->bucket($bucketName);
        $bucket->upload(
            fopen($filegif,'r'),
            [
                'predefinedAcl' => 'publicRead'
            ]
        );
        $bucket->upload(
            fopen($filethumbnail,'r'),
            [
                'predefinedAcl' => 'publicRead'
            ]
        );
        $bucket->upload(
            fopen($fileVideo,'r'),
            [






                
                'predefinedAcl' => 'publicRead'
            ]
        );
        $thumbnail="https://storage.googleapis.com/$bucketName/$filethumbnail";
        $gif="https://storage.googleapis.com/$bucketName/$filegif";
        $Video="https://storage.googleapis.com/$bucketName/$fileVideo";
        $video->thumbnail=$thumbnail;
        $video->gif=$gif;
        $video->shortVideo=$Video;
        $video->save();
        File::delete(base_path(str_replace('/', '\\', "storage/app/".$file)));
        return $video;
    }

}
