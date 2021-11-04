<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\User;
use App\Model\Role;
use App\Center\GridCenter;
use App\Transformer\UserTransformer;
use Intervention\Image\ImageManager;
use Auth;
use File;

class UserController extends Controller {
    public function index(Request $request) {
        return view('admin.user.index', []);
    }

    public function get_list(Request $request){
        $query = User::select('users.*', 'roles.code as role_code', 'roles.name as role_name')
            ->join('roles','roles.id','=','users.role_id');
        if(!empty($_GET['username'])){
            $query->where('username', 'like', '%'.$request->username.'%');
        }
        if(!empty($_GET['name'])){
            $query->where('users.name', 'like', '%'.$request->name.'%');
        }
        if(!empty($_GET['email'])){
            $query->where('email', 'like', '%'.$request->email.'%');
        }
        if(!empty($_GET['phone'])){
            $query->where('phone', 'like', '%'.$request->phone.'%');
        }
        if(!empty($_GET['employee_id'])){
            $query->where('employee_id', 'like', '%'.$request->employee_id.'%');
        }
        if(!empty($_GET['role_name'])){
            $query->where('roles.name', 'like', '%'.$request->role_name.'%');
        }
        if(!empty($_GET['status'])){
            $query->where('status', 'like', '%'.$request->status.'%');
        }
        $data = new GridCenter($query, $_GET);
    
        echo json_encode($data->render(new UserTransformer()));
        exit;
    }

    public function create() {   
        $res = Role::get(['id','code','name']);
        $roles[''] = 'Select Role';
        foreach($res as $o){
            $roles[$o->id] = $o->name;
        }
        return view('admin.user.create', [
            'roles'     => $roles
        ]);
    }

    public function store(Request $request) {
        $post = $request->all();
        $file = $request->file('image_file');
        $image = new ImageManager();
        // VALIDATE
        $valid = Validator::make($post, 
            [
                'email'         => 'required|email',
                'password'      => 'required|min:6|confirmed',
                'username'      => 'required',
                'name'          => 'required',
                'role_id'       => 'required'
            ]);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = User::where('username', '=', $request->username)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->username . " already exist!");
        }
        $isUsed = User::where('email', '=', $request->email)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->email . " already exist!");
        }

        $thumb_path = "";
        $img_path = "";
        if($request->hasFile('image_file')){
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); //get filename without extension
            $extension = $file->getClientOriginalExtension();
            $path = '/img/avatar';
            $path_thumb = '/img/avatar_thumb';
            $filenameCombine = $filename.'_'.time().'.'.$extension;
            $thumb_path = $path_thumb.'/'.$filenameCombine;
            $img_path = $path.'/'.$filenameCombine;
        }

        try{
            $user = new User;
            $user->username         = $request->input('username');
            $user->name             = $request->input('name');
            $user->email            = $request->input('email');
            $user->phone            = $request->input('phone');
            $user->role_id          = $request->input('role_id');
            $user->employee_id      = $request->input('employee_id');
            $user->password         = bcrypt($request->input('password'));
            $user->status           = 'active';
            if($request->hasFile('image_file')){
                //Create thumbnail and upload
                $destinationPath = public_path($path_thumb);
                $image->make($file->getRealPath())->resize(100, 100)
                    ->save($destinationPath.'/'.$filenameCombine);
                //Upload the original image
                $destinationPath = public_path($path);
                $file->move($destinationPath, $filenameCombine);
                $user->avatar_thumb = $thumb_path;
                $user->avatar = $img_path;
            }

            $user->save();

        }catch(Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
        }

         return redirect('admin/user')->with('message', 'Saved successfully');
    }

    public function show($id) {
        //
    }

    public function edit($id) {
        $user = User::select('users.*', 'roles.code as role_code')
            ->join('roles','roles.id','=','users.role_id')
            ->where('users.id', $id)
            ->first();
        $res = Role::get(['id','code','name']);
        $roles[''] = 'Select Role';
        foreach($res as $o){
            $roles[$o->id] = $o->name;
        }
        return view('admin.user.edit', [
            'user'      => $user, 
            'roles'     => $roles
        ]);
    }

    public function myprofile(){
        $user = User::select('users.*', 'roles.code as role_code', 'roles.name as role_name')
            ->join('roles','roles.id','=','users.role_id')
            ->where('users.id', Auth::user()->id)
            ->first();
        $res = Role::get(['id','code','name']);
        $roles[''] = 'Select Role';
        foreach($res as $o){
            $roles[$o->id] = $o->name;
        }
        return view('admin.user.myprofile', [
            'user'      => $user, 
            'roles'     => $roles
        ]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
        $file = $request->file('image_file');
        $image = new ImageManager();
        // VALIDATE
        $valid = Validator::make($post, 
            [
                'email'         => 'required|email',
                'username'      => 'required',
                'name'          => 'required',
                'role_id'       => 'required'
            ]);

        // CHECK AVAILABILITY
        $isUsed = User::where('username', '=', $request->username)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->username . " already exist!");
        }
        $isUsed = User::where('email', '=', $request->email)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->email . " already exist!");
        }

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        if(!empty($request->input('password'))){
            $valid = Validator::make($post, ['password' => 'required|min:6|confirmed']);
            if($valid->fails()){
                return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
            }
        }

        $thumb_path = "";
        $img_path = "";
        if($request->hasFile('image_file')){
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME); //get filename without extension
            $extension = $file->getClientOriginalExtension();
            $path = '/img/avatar';
            $path_thumb = '/img/avatar_thumb';
            // $filenameCombine = $filename.'_'.time().'.'.$extension;
            // $thumb_path = $path_thumb.'/'.$filenameCombine;
            // $img_path = $path.'/'.$filenameCombine;
        }

        try{
            $user = User::find($id);
            $user->username         = $request->input('username');
            $user->name             = $request->input('name');
            $user->email            = $request->input('email');
            $user->phone            = $request->input('phone');
            $user->role_id          = $request->input('role_id');
            $user->employee_id      = $request->input('employee_id');
            //$user->status           = 'active';
            if($request->hasFile('image_file')){
                $filenameCombine = 'USR-'.$user->id.'.'.$extension;
                $thumb_path = $path_thumb.'/'.$filenameCombine;
                $img_path = $path.'/'.$filenameCombine;
                
                //Create thumbnail and upload
                $destinationPath = public_path($path_thumb);
                $image->make($file->getRealPath())->resize(100, 100)
                    ->save($destinationPath.'/'.$filenameCombine);
                //Upload the original image
                $destinationPath = public_path($path);
                $file->move($destinationPath, $filenameCombine);

                // if(!empty($user->avatar_thumb)){
                //     File::delete(public_path($user->avatar_thumb));
                // }

                // if(!empty($user->avatar)){
                //     File::delete(public_path($user->avatar));
                // }

                $user->avatar_thumb = $thumb_path;
                $user->avatar = $img_path;
            }

            if(!empty($request->input('password')) && !empty($request->input('password_confirmation'))){
                $user->password = bcrypt($request->input('password'));
            }

            $user->save();

        }catch(Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
        }
        if($request->input('page_name') == 'myprofile'){
            return redirect('my_user_account')->with('message', 'Updated successfully');
        } else {
            return redirect('admin/user')->with('message', 'Updated successfully');
        }
    }

    public function destroy($id) {
        try{
            //User::destroy($id);
            $user = User::find($id);
            $user->status = 'inactive';
            $user->save();

            return redirect('admin/user')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function activate($id) {
        try{
            //User::destroy($id);
            $user = User::find($id);
            $user->status = 'active';
            $user->save();

            return redirect('admin/user')->with('message', 'Activated successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
