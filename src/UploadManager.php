<?php
declare (strict_types=1);

namespace Intoy\HebatPsr7;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;

class UploadManager 
{
    public const ALLOWS_EXT_ALL=[
        "txt",
        "pdf",
        "xls",
        "xlsx",
        "doc",
        "docx",
        "bmp",
        "jpg",
        "jpeg",
        "png",
        "rar",
        "zip",
    ];

    public const ALLOWS_EXT_IMAGE=[
        "bmp",
        "jpg",
        "jpeg",
        "png",
        "webp", 
    ];

    public const ALLOWS_EXT_IMAGE_RESIZE=[
        "bmp",
        "jpg",
        "jpeg",
        "png",
    ];
    /**
     * @var array<UploadedFileInterface>
     */
    protected $items=[];


    public function __construct(Request $request)
    {
        $this->items=$request->getUploadedFiles();        
    }

    /**
     * Get Uploaded files by key_name $_FILES
     * @param string $name
     * @return null|UploadedFileInterface
     */
    public function getByName(string $name)
    {
        return isset($this->items[$name])?$this->items[$name]:null;
    }


    /**
     * Get some name is included in uploaded
     * @param string $name
     * @return bool
     */
    public function hasUpload(string $name)
    {
        $f=$this->getByName($name);
        if($f instanceof UploadedFileInterface)
        {
            return $f->getError()===UPLOAD_ERR_OK;
        }

        return false;
    }


    /**
     * Get some name is included in uploaded
     * @param string $name
     * @param array $extensions 
     * @return bool
     */
    public function hasAllowExtensions(string $name, array $extensions)
    {
        $f=$this->getByName($name);
        if($f instanceof UploadedFileInterface)
        {
            $ok=$f->getError()===UPLOAD_ERR_OK;
            if(!$ok){
                return $ok;
            }

            $ext=pathinfo($f->getClientFilename(),PATHINFO_EXTENSION);
            $ext=strtolower($ext);
            return in_array($ext, $extensions);
        }

        return false;
    }


    /**
     * Move upload to directory with name
     * @param string $name string $_FILES key
     * @param string $directory string directory name
     * @param string $newFileName string new name of file
     * @return string
     * @throws \Exception
     */
    public function move(string $name, string $directory, string $newFileName=""):string
    {
        $f=$this->getByName($name);
        if(!$f instanceof UploadedFileInterface)
        {
            throw new \Exception('Invalid params. Use params instance of ServerRequestInface or UploadedFileInstarface.');
        }

        $directory=rtrim($directory,DIRECTORY_SEPARATOR);
        if(!is_dir($directory))
        {
            throw new \Exception('Directory target not exists.');
        }

        $ext=pathinfo($f->getClientFilename(),PATHINFO_EXTENSION);
        $ext=strtolower($ext);

        if(!$newFileName)
        {
            $basename = bin2hex(random_bytes(8));
            $newFileName = sprintf('%s.%0.8s', $basename, $ext);
        }
        else {
            /// test extension
            $newFileName=explode(".",$newFileName);
            if(count($newFileName)>0)
            {
                $last=strtolower(trim((string)end($newFileName)));
                $newFileName=implode(".",$newFileName);
                if($last!==$ext){
                    $newFileName.=".".$ext;
                }
            }
            else {
                $newFileName=implode(".",$newFileName).".".$ext;
            }
        }

        $f->moveTo($directory.DIRECTORY_SEPARATOR.$newFileName);
        return $newFileName;
    }


    public static function removeFile(string $directory, $filename=""):bool
    {
        $filename=trim((string)$filename);
        $fullfilename=$directory.$filename;
        if(strlen($filename)>0 
        && file_exists($fullfilename)
        ){
            unlink($fullfilename);
            return true;
        }
        return false;
    }
}