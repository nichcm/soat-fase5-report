<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TriggerServiceConnectionController
{
    public function data(Request $request)
    {
        return response()->json(
            [
                "err" => false,
                "msg" => "...",
            ],
            Response::HTTP_OK,
        );
    }

    public function status(Request $request)
    {
        return response()->json(
            [
                "err" => false,
                "msg" => "...",
            ],
            Response::HTTP_OK,
        );
    }
}
