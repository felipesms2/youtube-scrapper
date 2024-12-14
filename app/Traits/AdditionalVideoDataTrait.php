<?php

namespace App\Traits;

use App\Models\Thumbnail;
use App\Models\Video;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\fileExists;

trait AdditionalVideoDataTrait
    {

        public function listFiles()
        {
            $s = storage_path() . "/app/videos/";
            $v = Video::all();
            $videoData = [];
            foreach ($v as $obj)
            {
                // $fullPath = ($s . $obj . ".mp4");
                // dump($s . $obj->id . ".mp4");
                // file_exists($fullPath)? dump("existe") : dump("não");
                $videoData[$obj->id] =
                [

                    'filename'=> $obj->id . ".mp4",
                    'video_id' =>$obj->id,
                    'title' =>$obj->title,
                    'description' =>$obj->description,
                ] ;
            }
            return $videoData;

        }
        public function prepareThumb($thumbData, $video_id = 0)
        {
            $thumbToInsert =[];
            $thumbData = (array)$thumbData;
            $bunchThumbLabel = array_keys($thumbData);
            foreach ($bunchThumbLabel as $r)
            {
                $thumbDataConverted =(array)$thumbData[$r];
                $thumbDataConverted['title_thumb'] = $r;
                $thumbDataConverted['video_id'] = $video_id;
                $thumbToInsert[] = $thumbDataConverted;
            }
            return ($thumbToInsert);
        }

        public function setVideoQueryString($unitGroup25)
        {

                $queryId = "";
                foreach ($unitGroup25 as $u)
                {
                    $queryId .= "&id=" . $u ;
                    $endpoint = "https://www.googleapis.com/youtube/v3/videos?part=id%2C+snippet&key=AIzaSyBB93pQFssH3DTGXfROEojYCJARPOzQj04&" . $queryId .
                    "";
                }

             return($endpoint);
        }
        public function setBunchId($csvFile)
        {
            $file = $csvFile;
            $bunchID = [];
            while(! feof($file))
            {
                if( $text =fgetcsv($file))
                {
                    $bunchID[] = $text[0];
                }
            }
            fclose($file);
            return $bunchID;

        }

        public function buildVideoArray($obtainedData)
        {
            $data = $obtainedData;
            foreach ($data->items as $item)
            {
                $sni = $item->snippet;
                $sni->defaultAudioLanguage = "NONE";
                $titles[]= $sni->title;
                $title = $sni->title;
                $channelId = $sni->channelId;
                $kind=$item->kind;
                $etag=$item->etag;
                $video_id=$item->id;
                $publishedAt = Carbon::parse($sni->publishedAt, 'UTC');
                $description = $sni->description;
                $categoryId = $sni->categoryId;
                if(isset($sni->defaultAudioLanguage))
                {
                    $defaultAudioLanguage = $sni->defaultAudioLanguage;
                }
                $liveBroadcastContent = $sni->liveBroadcastContent;
                $arrayToInsert = [
                    "title" => $title,
                    "channelId" => $channelId,
                    "kind" => $kind,
                    "video_id" => $video_id,
                    "etag" => $etag,
                    "publishedAt" => $publishedAt,
                    "description" => $description,
                    "categoryId" => $categoryId,
                    "defaultAudioLanguage" => $defaultAudioLanguage,
                    "liveBroadcastContent" => $liveBroadcastContent,
                ];
                return $arrayToInsert;
            }

        }

        public function downloadThumbs(){
            $thumbs = Thumbnail::all();
            // $missing = [
            //     588,589,590,591,592,593,594,595,596,597,598,599,600,601,602,603,604,605,606,607,608,609,610,611,612,613,614,615,616,617,618,619,620,621,622,623,624,625,626,627,628,629,630,631,632,633,634,635,636,637,638,639,640,641,642,643,644,645,646,647,648,649,650,651,652,1995,1996,1997,1998,1999
            // ];
            foreach ($thumbs as $t) {
                // Perform actions on each item
                // You can access the item's properties using $item->property
                // if (  in_array($t->video_id, $missing))
                if ($t->video_id >=0)
                {
                    echo /* $t->url . */ " - " .$t->video_id . " ";
                    shell_exec("clear");
                    $client = new Client();
                    $response = $client->get($t->url);
                    $folder = 'tmp/fraga';
                    $fileName = $t->video_id . "_" . $t->title_thumb . '.jpg'; // Replace with the desired file name and extension
                    Storage::put($folder.'/'.$fileName, $response->getBody());
                }
            }
        }

        public function downloadVideo()
        {
            $video = Video::all();
            foreach ($video as $v)
            {
                if ($v->id >0)
                {
                    $urlPrefix ="youtube.com/watch?v=";
                    $urlSufix = $v->video_id;
                    $contentNumber = $v->id;
                    $placeToSave = ( __DIR__ . "../../../storage/app/videos/tmp");
                    shell_exec("
                        source ~/general_use/bin/activate
                        cd $placeToSave;
                        yt-dlp " . $urlPrefix . $urlSufix . ";" .
                        "mv *.mp4 ../$contentNumber.mp4; mv *.webm ../$contentNumber.webm"
                    );
                }

                // die("baixou");
                echo $v->id . " - ";
            }
        }

    }

