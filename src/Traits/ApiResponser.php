<?php

namespace AcidWave\LaravelSSO\Traits;

use Illuminate\Http\Response;

trait ApiResponser
{
    /**
     * Build success response
     *
     * @param  string|array $data
     * @param  int $code
     * @param  array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, $code = Response::HTTP_OK, array $headers = [])
    {
        return response()->json(['data' => $data], $code, $headers);
    }


    /**
     * Build error response
     *
     * @param  string|array $data
     * @param  int $code
     * @param  array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($message, $code, array $headers = [])
    {
        return response()->json(['error' => $message, 'code' => $code], $code, $headers);
    }
}
