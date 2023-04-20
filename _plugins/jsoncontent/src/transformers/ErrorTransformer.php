<?php

namespace thisisablock\jsoncontent\transformers;

class ErrorTransformer
{
    
    static function transform($error = 404)
    {
        
        $data = [];
        if ($error == 404) {
            return [
                "message" => "Die Seite wurde leider nicht gefunden.",
                "code" => $error
            ];
        }
        else {
            die('error');
        }
        
        
        return $data;
    }
}
