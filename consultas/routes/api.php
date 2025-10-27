<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', function (Request $r) {
    $cred = $r->validate(['email'=>'required|email','password'=>'required']);
    if (!$token = auth('api')->attempt($cred)) {
        return response()->json(['message'=>'Unauthorized'], 401);
    }
    return response()->json(['token'=>$token]);
});

Route::middleware('auth:api')->get('/auth/me', fn() => auth('api')->user());