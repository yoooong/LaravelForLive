<?php
namespace App\Handlers;


use Exception;
use Illuminate\Support\Facades\Storage;

class UploadHandler
{
    protected $file;
    protected $allowed_extensions = ["png", "jpg", "gif", 'jpeg','mp4','mov','m4v','mpg'];

    public function __construct()
    {
        $this->disk = Storage::disk('oss');
    }


    public function upload($file)
    {
        $this->file = $file;
        $this->checkAllowedExtensions();

        $file_name = $this->saveFileToOss($file->getPathName());
        return $file_name;
    }

    public function checkAllowedExtensions()
    {
        $extension = strtolower($this->file->getClientOriginalExtension());
        if ($extension && !in_array($extension, $this->allowed_extensions)) {
            throw new Exception('格式出错');
        }

    }

    public function saveFileToOss($pathname)
    {
        $extension = $this->file->getClientOriginalExtension();
        $safeName = "/lpsp/" . date("Ym", time()) . '/' . date("dHis", time()) . '.' . $extension;
        if ($this->disk->put($safeName, \File::get($pathname))) {

            return $safeName;
        };
    }

}