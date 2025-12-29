<?php

namespace App\Traits;
use App\Http\Resources\PaginationResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    protected function apiResponse(
        array|Collection|JsonResource|ResourceCollection|LengthAwarePaginator|Model $data = [],
        array|Collection $errors = [],
        string $msg = '',
        bool $status = true,
        int $code = 200,
        $pagination = null
    ) {

        $response = [
            'status' => $status,
            'message' => $msg,
            'data' => $data,

        ];

        if ($pagination instanceof LengthAwarePaginator) {
            $response['pagination'] = new PaginationResource($pagination);
        }
        if (count($errors) > 0) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $code);
    }
}
