<?php

namespace App\Observers;

use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        $name = 'avatars/' . $name . '.' . $ext;
        $user->photo->storeAs('', $name);
        $user->photo = $name;
    }

    protected function isValidFile($photo)
    {
        return $photo instanceof UploadedFile && $photo->isValid();
    }
}
