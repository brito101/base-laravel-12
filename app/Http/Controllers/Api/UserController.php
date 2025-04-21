<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CheckPermission;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Views\User as ViewsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function __construct()
    {
        try {
            if (! JWTAuth::parseToken()->authenticate()) {
                throw new \Exception('User not found', 404);
            }
        } catch (JWTException $e) {
            throw new \Exception('Invalid token', 400);
        }
    }

    public function index(Request $request)
    {
        CheckPermission::checkAuth('Listar Usuários');

        /** @var User $user */
        $user = Auth::user();
        if ($user->hasRole('Programador')) {
            $users = ViewsUser::all('id', 'name', 'email', 'type', 'photo');
        } elseif ($user->hasRole('Administrador')) {
            $users = ViewsUser::whereIn('type', ['Administrador', 'Usuário'])->get(['id', 'name', 'email', 'type', 'photo']);
        } else {
            $users = null;
        }

        foreach ($users as $user) {
            if ($user->photo) {
                $user->photo = url('/storage/users/'.$user->photo);
            }
        }

        return response()->json(compact('users'));
    }

    public function profile()
    {
        CheckPermission::checkAuth('Listar Usuários');

        /** @var User $user */
        $user = ViewsUser::find(Auth::user()->id);
        $profile = $user->only('id', 'name', 'email', 'type', 'photo');

        if ($profile['photo']) {
            $profile['photo'] = url('/storage/users/'.$profile['photo']);
        }

        return response()->json(compact('user'));
    }
}
