<?php

namespace App\Observers;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Image;

class UserPhotoObserver
{
    public function creating(User $user)
    {
        if ($this->isValidFile($user->photo)) {
            $this->upload($user);
        }
    }

    public function deleting(User $user)
    {
        Storage::delete($user->photo);
    }

    public function updating(User $user)
    {
        if ($this->isValidFile($user->photo)) {
            $previous_image = $user->getOriginal('photo');
            $this->upload($user);
            Storage::delete($previous_image);
        }
    }

    protected function upload(User $user)
    {
        $ext = $user->photo->extension();
        if (!in_array($ext, ['jpg', 'jpeg','gif', 'png'])) {
            throw new \Exception('Extension not allowed!');
        }
        $name = bin2hex(openssl_random_pseudo_bytes(16));
        $name .= '.' . $ext;
        $user->photo = $this->processPhoto($user->photo, $name);
    }

    protected function processPhoto($photo, $new_name)
    {
        $dir = 'avatars/';
        $tmp_dir = $dir.'swp/';
        $photo->storeAs('', $tmp_dir . $new_name);
        $img = Image::make($photo->getRealPath());
        $img->fit(120, 120)->save($dir . $new_name);
        Storage::delete($tmp_dir . $new_name);
        return $dir . $new_name;
    }

    protected function isValidFile($photo)
    {
        return $photo instanceof UploadedFile && $photo->isValid();
    }
}
