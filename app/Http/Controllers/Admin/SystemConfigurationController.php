<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Model\SystemConfiguration;
use App\Model\SystemConfigurationPermission;
use App\Model\Permission;
use App\Model\PermissionGroup;
use App\Center\GridCenter;
use App\Transformer\SystemConfigurationTransformer;

class SystemConfigurationController extends Controller {
    public function index() {
        $data = SystemConfiguration::get();
        return view('admin.system_configuration.index', [
            'data' => $data
        ]);
    }

    public function get_list(){
        $query = SystemConfiguration::select();
        $data = new GridCenter($query, $_GET);
    
        echo json_encode($data->render(new SystemConfigurationTransformer()));
        exit;
    }

    public function create()
    {
        return view('admin.system_configuration.create', []);
    }

    public function store(Request $request) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = [
            'code'          => 'required',
            'description' 	=> 'required',
            'value' 		=> 'required'
        ];
        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = SystemConfiguration::where('code', '=', $request->code)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->code . " already exist!");
        }

        try{
            $system_configuration = new SystemConfiguration;
            $system_configuration->code 		= $request->input('code');   
            $system_configuration->description 	= $request->input('description');  
            $system_configuration->value 		= $request->input('value'); 
            $system_configuration->save();

        }catch(Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('admin/system_configuration')->with('message', 'Saved successfully');
    }

    public function show($id) {
        //
    }

    public function edit($id) {
        $data = SystemConfiguration::find($id);
        return view('admin.system_configuration.edit', [
            'data'              => $data
        ]);
    }

    public function update(Request $request, $id) {
        $post = $request->all();
        // VALIDATE
        $validated_fields = [
            'code'          => 'required',
            'description' 	=> 'required',
            'value' 		=> 'required'
        ];
        $valid = Validator::make($post,$validated_fields);

        if($valid->fails()){
            return redirect()->back()->withInput($request->input())->withErrors($valid->errors());
        }

        // CHECK AVAILABILITY
        $isUsed = SystemConfiguration::where('code', '=', $request->code)->where('id', '<>', $id)->first();
        if ($isUsed !== null) {
            return redirect()->back()->withInput($request->input())->withErrors($request->code . " already exist!");
        }
        try{
            $system_configuration = SystemConfiguration::find($id);
            $system_configuration->code 		= $request->input('code');   
            $system_configuration->description 	= $request->input('description');  
            $system_configuration->value 		= $request->input('value'); 
            $system_configuration->save();

        }catch(Exception $e){
            return redirect()->back()->withErrors($e->getMessage());
        }

        return redirect('admin/system_configuration')->with('message', 'Saved successfully');
    }

    public function destroy($id) {
        try{
            $system_configuration = SystemConfiguration::find($id);
            $system_configuration->delete();
            return redirect('admin/system_configuration')->with('message', 'Deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }
}
