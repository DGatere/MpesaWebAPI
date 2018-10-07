<?php

namespace App\Traits;

trait ApiResponser
{
    protected function errorResponse($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }
}
