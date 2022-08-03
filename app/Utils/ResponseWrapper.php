<?php
namespace App\Utils;

class ResponseWrapper{
    public static function response($data,$message='Success'){
        return response()->json([
            'message'=> $message,
            'data'=> $data
        ]);
    }
    public static function deleteResponse($id,$message=null){
        $message= $message??'Success!, item has been deleted successfully!';
        return response()->json([
            'message'=> $message,
            'data'=> $id
        ]);
    }
    public static function errorResponse($message, $code){
        return response()->json([
            'message'=> $message
        ],$code);
    }
}