<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model {

    protected $fillable = ['first_name', 'last_name', 'email'];

    protected $appends = array('image_url');

    const PLACEHOLDER_IMG = 'place_holder.jpg';

 /* *************************
        Name :   Admin
        Description : This model class allow access to some action
        Author : Gyanendra Singh
       ************************* */

      /* Create foldername to store image here*/

    public static function getStorageFolderName() {
        return 'Admin Image';
    }
      /* Get Image */
    public function getImageUrlAttribute() {

        if ($this->exists) {
            $image_path = \App\Facades\Tools::getStorageDirectoryPath(self::getStorageFolderName() . '/' . $this->id);

            if (\Illuminate\Support\Facades\File::exists($image_path . $this->image) && !empty($this->image)) {
                $url = \App\Facades\Tools::getStorageUrl(self::getStorageFolderName() . '/' . $this->id) . $this->image;
            } else {
                $url = asset('images/' . self::PLACEHOLDER_IMG);
            }
            return $url;
        }
        return asset('images/' . self::PLACEHOLDER_IMG);
    }

    /*Uplode Image*/

    public function uploadImage() {
        if (!$this->exists) {
            return;
        }
        $path = \App\Facades\Tools::getStorageDirectoryPath(self::getStorageFolderName() . '/' . $this->id);

        if (\Illuminate\Support\Facades\File::exists($path . $this->image) && !empty($this->image)) {
            \Illuminate\Support\Facades\File::delete($path . $this->image);
        }

        if (!\Illuminate\Support\Facades\File::isDirectory($path)) {
            \Illuminate\Support\Facades\File::makeDirectory($path, 0755, true);
        }

        $storage = new \Upload\Storage\FileSystem($path);

        $file = new \Upload\File('image', $storage);

        $new_name = time();
        $file->setName($new_name);

        $data = array(
            'name' => $file->getNameWithExtension(),
            'extension' => $file->getExtension(),
            'mime' => $file->getMimetype(),
            'size' => $file->getSize(),
            'md5' => $file->getMd5(),
            'dimensions' => $file->getDimensions()
        );

        // Try to upload file
        try {
            // Success!
            $file->upload();
            $this->image = $data['name'];
            $this->save();
        } catch (\Exception $e) {
            // Fail!
            $errors = $file->getErrors();
            //print_r($errors);die;
        }
    }

}
