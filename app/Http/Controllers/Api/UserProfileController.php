<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LayoutConfig;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)
            ->first();

        if (!$user) {
            return response()->json([
                'code'    => 404,
                'message' => '找不到該使用者',
                'data'    => null,
            ], 404);
        }

        $portfolios = $user->portfolios()
            ->with('images')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($p) {
                return [
                    'id'          => $p->id,
                    'name'        => $p->name,
                    'description' => $p->description,
                    'url'         => $p->url,
                    'images'      => $p->images->map(function ($img) {
                        return [
                            'id'         => $img->id,
                            'image_path' => $img->image_path,
                        ];
                    }),
                ];
            });

        $layoutConfig = LayoutConfig::where('user_id', $user->id)->first();
        $themeStyle   = $layoutConfig ? $layoutConfig->theme_style : 'dark_star';

        return response()->json([
            'code'    => 200,
            'message' => 'ok',
            'data'    => [
                'user'         => $user->only([
                    'id', 'username', 'nickname', 'email', 'phone',
                    'title', 'bio', 'avatar', 'location',
                ]),
                'portfolios'   => $portfolios,
                'skills'       => $user->skills,
                'experiences'  => $user->workExperiences,
                'social_links' => $user->socialLinks,
                'layout'       => ['theme_style' => $themeStyle],
            ],
        ]);
    }
}
