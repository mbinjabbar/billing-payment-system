<?php

namespace App\Traits;


use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    protected function success(mixed $data, string $message = 'Success', int $code = 200, $stats = null): JsonResponse
    {

        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
                'from'         => $data->firstItem(),
                'to'           => $data->lastItem(),
            ];
        }

     if ($stats) {
        $response['stats'] = $stats;
    }

        return response()->json($response, $code);
    }

    protected function error(string $message = 'Error', int $code = 500, mixed $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
