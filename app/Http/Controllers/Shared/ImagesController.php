<?php

namespace App\Http\Controllers\Shared;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImageRequest;
use Intervention\Image\ImageManagerStatic as Image;

use App\Http\Traits\ResponseUtilities;

use Exception;

class ImagesController extends Controller
{
    use ResponseUtilities;

    private $baseURL;
    private $path;
    private $threshold;
    private $thumbnailWidt;

    public function __construct(){
        
        $this->baseURL = \URL::to('/') . '/' .config('constants.file_uploading.image_storage_path');
        $this->path = public_path() . '/' .config('constants.file_uploading.image_storage_path');
        $this->threshold = 1024 * (int) config('constants.file_uploading.image_size_threshold_mb') * 1000;
        $this->thumbnailWidt = (int) config('constants.file_uploading.image_thumbnail_width_px');
    }

    public function store(StoreImageRequest $request)
    {

        if (!file_exists($this->path)) { mkdir($this->path); }

        $this->data['code'] = 400;
        $this->data['message'] = __('messages.uploading_failed');

        try{
            if($request->hasFile('image')) {

                //Get file name with the extension
                $fileNameWithExt = $request->file('image')->getClientOriginalName();
                //get just file name
                $fileName=pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                //Get just extension
                $extension=$request->file('image')->getClientOriginalExtension();
                //file name to store
                $uniqueFileNameWithoutExt = $fileName.'_'.time();
                $thumbnailWithExt = $uniqueFileNameWithoutExt.'_'.config('constants.file_uploading.image_thumbnail_suffix').'.'.$extension;
                $uniqueFileNameWithExt = $uniqueFileNameWithoutExt.'.'.$extension;

                $image = Image::make($request->file('image')->getRealPath());
                $image->save(
                    config('constants.file_uploading.image_storage_path').$uniqueFileNameWithExt,
                    $this->getPercentageToMaxQuality($image->filesize(), $this->threshold)
                );

                $thumbnail = Image::make($request->file('image')->getRealPath());
                $thumbnail->resize($this->thumbnailWidt, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $thumbnail->save(
                    config('constants.file_uploading.image_storage_path') . $thumbnailWithExt,
                    $this->getPercentageToMaxQuality($thumbnail->filesize(), $this->threshold)
                );


                $data= [
                    'image'=>$this->baseURL.$uniqueFileNameWithExt,
                    'thumbnail'=>$this->baseURL.$thumbnailWithExt
                ];
                $this->initResponse(200, 'uploading_success', $data);

            }
        }catch(Exception $e){
            $this->initErrorResponse($e);
        }
        return response()->json($this->data, 200);
    }

    /**
     * This function returns the best quality percentage to get an image size <= $threshold
     */
    protected function getPercentageToMaxQuality($currQuality, $threshold){
        if($currQuality>$threshold){
            return 100 - (int)((($currQuality-$threshold)/$currQuality)*100);
        }
        return 89;
    }
}