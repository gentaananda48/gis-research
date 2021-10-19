<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\Role;
use App\Model\RolePermission;
use App\Model\Permission;
use App\Model\PermissionGroup;
use App\Center\GridCenter;
use App\Transformer\RoleTransformer;

class RoleController extends Controller {
    public function index() {
        $data = Role::get();
        return view('admin.role.index', [
            'data' => $data
        ]);
    }

    public function get_list(){
        $query = Role::select();
        $data = new GridCenter($query, $_GET);
    
        echo json_encode($data->render(new RoleTransformer()));
        exit;
    }

    public function create()
    {
        return view('admin.role.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = [
            'code'          => 'required',
            'name'          => 'required'
        ];
        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = Role::where('code', '=', $request->code)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->code . " already exist!");
        }

        try{
            $role = new Role;
            $role->code             = $request->input('code');   
            $role->name             = $request->input('name');   
            $role->save();

        }catch(Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('admin/role')->with('message', 'Saved successfully');
    }

    public function show($id) {
        //
    }

    public function edit($id) {
        $data = Role::find($id);
        return view('admin.role.edit', [
            'data'              => $data
        ]);
    }

    public function permission($id){
        $res = Permission::join('permission_groups as pg', 'pg.id', '=', 'permissions.group_id')
            ->orderBy('pg.no','ASC')
            ->orderBy('permissions.no','ASC')
            ->get(['permissions.id','permissions.name','permissions.group_id','pg.name as group_name', 'pg.icon']);
        $count_permissions = 0;
        $permissions = [];
        foreach($res as $v){
            $count_permissions++;
            $permissions[$v->group_name]['icon'] = $v->icon;
            $permissions[$v->group_name]['data'][] = $v;
        }
        $role = Role::find($id);
        $res = RolePermission::where('role_id', $id)->get(['permission_id']);
        $count_role_permissions = 0;
        $role_permissions = [];
        foreach($res as $o){
            $count_role_permissions++;
            $role_permissions[] = $o->permission_id;
        }
        $is_all_checked = false;
        if($count_permissions == $count_role_permissions){
            $is_all_checked = true;
        }
        return view('admin/role/permission',[
            'role'                  => $role,
            'permissions'           => $permissions, 
            'role_permissions'      => $role_permissions,
            'is_all_checked'        => $is_all_checked
        ]);
    }

    public function updatePermission(Request $request, $id){
        $permissions = Permission::orderBy('no','ASC')->get(['id']);
        $res = RolePermission::where('role_id', $id)->get(['permission_id']);
        $role_permissions = [];
        foreach($res as $o){
            $role_permissions[] = $o->permission_id;
        }
        foreach($permissions as $o){
            if(!empty($request->permission[$o->id]) && $o->id == $request->permission[$o->id]){
                $permission = RolePermission::where('role_id', $id)->where('permission_id', $o->id)->first();
                if(empty($permission)){
                    RolePermission::create(['role_id' => $id, 'permission_id' => $o->id]);
                }
            } else {
                $permission = RolePermission::where('role_id', $id)->where('permission_id', $o->id)->first();
                if(!empty($permission)){
                    RolePermission::find($permission->id)->delete();
                }
            }
        }
        
        return redirect('admin/role/permission/'.$id)->with('message', 'Saved successfully');
    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = [
            'code'          => 'required',
            'name'          => 'required'
        ];
        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = Role::where('code', '=', $request->code)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->code . " already exist!");
        }
        try{
            $role = Role::find($id);
            $role->code             = $request->input('code');
            $role->name             = $request->input('name');
            $role->save();

        }catch(Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('admin/role')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $role = Role::find($id);
            $role->delete();
            return redirect('admin/role')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
