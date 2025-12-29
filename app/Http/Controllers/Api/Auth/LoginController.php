<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Services\Website\ProductService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
class LoginController extends Controller
{
    use ApiResponse;
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || Hash::check($request->password, $user->password) == false) {

            return $this->apiResponse([], [], 'Invalid credentials', false, 401);
        }
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'token' => $token,
            'user' => UserResource::make($user)
        ];
        return $this->apiResponse($data, [], 'You have successfully logged in', true, 200);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->apiResponse([], [], 'You have successfully logged out', true, 200);
    }
}
