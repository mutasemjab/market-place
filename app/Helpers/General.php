<?php

function uploadImage($folder, $image)
{
    $extension = strtolower($image->getClientOriginalExtension());

    // Generate a unique filename
    $filename = time() . '_' . uniqid() . '.' . $extension;

    $image->move($folder, $filename);

    return $filename;
}


function uploadFile($file, $folder)
{
    $path = $file->store($folder);
    return $path;
}



