<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserProfileController;

// 公開前台 API：透過 username 取得個人主頁資料
Route::get('/public/{username}', [UserProfileController::class, 'show'])
    ->where('username', '[a-zA-Z0-9_\-]+');
