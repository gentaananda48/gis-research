<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Model\User;
use App\Model\Permission;
use Illuminate\Support\Facades\Hash;
use App\Model\VUser;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'login2']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
          $user = $this->guard()->user();
          VUser::find($user->id);
          $user_profile = VUser::find($user->id);
          $permission = Permission::join('role_permission AS rp', 'rp.permission_id', '=', 'permissions.id')
            ->where('rp.role_id', $user->role_id)
            ->where('permissions.group_id', 5)
            ->get(['permissions.code']);
          $list_permission = [];
          foreach($permission as $v){
            $list_permission[] = $v->code;
          }
          $user_profile->permissions = implode(',',$list_permission);
          $data = [
            'user_info'     => $user_profile,
            'token'  => [
              'access_token'  => $token,
              'token_type'    => 'bearer',
              'expires_in'    => $this->guard()->factory()->getTTL() * 60
            ]
          ];
          // return $this->respondWithToken($token);
          return response()->json([
            'status' => true, 
            'message' => 'Signed In Successfully', 
            'data' => $data
          ]);
        }

        //return response()->json(['error' => 'Unauthorized'], 401);
        return response()->json(['status' => false, 'message' => 'Invalid credentials', 'data' => null], 200);
    }

    public function login2(Request $request)
    {
        $credentials = ['username' => $request->USERNAME, 'password' => $request->PASSWORD];
        if ($token = $this->guard()->attempt($credentials)) {
          $user = $this->guard()->user();

          $data = [
            'USER_INFO'     => (object) array_change_key_case(json_decode(json_encode($user), TRUE), CASE_UPPER),
            'TOKEN'  => [
              'ACCESS_TOKEN'  => $token,
              'TOKEN_TYPE'    => 'bearer',
              'EXPIRES_IN'    => $this->guard()->factory()->getTTL() * 60
            ]
          ];
          // return $this->respondWithToken($token);
          return response()->json([
            'STATUS' => true, 
            'MESSAGE' => 'Signed In Successfully', 
            'DATA' => $data
          ]);
        }

        //return response()->json(['error' => 'Unauthorized'], 401);
        return response()->json(['STATUS' => false, 'MESSAGE' => 'Invalid credentials', 'DATA' => json_encode($credentials).$request->username.$request->password], 200);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        // return response()->json([
        //     'access_token' => $token,
        //     'token_type' => 'bearer',
        //     'expires_in' => $this->guard()->factory()->getTTL() * 60
        // ]);
        return response()->json(['status' => true, 'message' => '', 'data' => [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]]);
    }

    /**
     * Change Password.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request){
      $authUser = $this->guard()->user();
      $user = User::find($authUser->id);
      $currentPassword = $request->input('currentPassword');
      $newPassword = $request->input('newPassword');
      if(empty($currentPassword)) {
        return response()->json([
          'status' => false, 
          'message' => 'currentPassword is required', 
          'data' => null
        ]);
      } 
      if(empty($newPassword)) {
        return response()->json([
          'status' => false, 
          'message' => 'newPassword is required', 
          'data' => null
        ]);
      } 
      if($currentPassword == $newPassword) {
        return response()->json([
          'status' => false, 
          'message' => 'currentPassword and newPassword must be different', 
          'data' => null
        ]);
      } 
      if(Hash::check($currentPassword, $user->password)) {
        // Right password
      } else {
        // Wrong one
        return response()->json([
          'status' => false, 
          'message' => 'currentPassword is invalid', 
          'data' => null
        ]);
      }
      $user->password = bcrypt($newPassword);
      $user->save();
      return response()->json([
        'status'    => false, 
        'message'   => 'Password Changed Successfully', 
        'data'      => null
      ]);
    }

    public function permission()
    {
      $user = $this->guard()->user();
      $permission = Permission::join('role_permission AS rp', 'rp.permission_id', '=', 'permissions.id')
        ->where('rp.role_id', $user->role_id)
        ->where('permissions.group_id', 5)
        ->get(['permissions.code']);
      $list_permission = [];
      foreach($permission as $v){
        $list_permission[] = $v->code;
      }
      return response()->json([
        'status' => true, 
        'message' => '', 
        'data' => $list_permission
      ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }
}