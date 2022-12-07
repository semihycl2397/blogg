<?php
namespace App\Helpers\ArkSigner;

class FileConverter{
    public static function getFileBase64($fileName){
        try{
            //convert file to base64Data
            $b64Doc = chunk_split(base64_encode(file_get_contents($fileName)));
        }
        catch (Exception $ex){
            echo 'Hata => ' , $ex->getMessage();
            $b64Doc = "";
        }

        return $b64Doc;
    }

}
