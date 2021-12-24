<?php

namespace App\Imports;

use App\Model\User;
use App\Model\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserImport implements ToCollection, WithHeadingRow
{
    function __construct() {}

    public function collection(Collection $rows) {
        set_time_limit(0);
        DB::beginTransaction();
        try {
            foreach ($rows as $k=>$row){
                $username 		= $row["username"];
                $name 			= $row["name"];
                $email 			= $row["email"];
                $phone 			= $row["phone"];
                $role_name 		= $row["role"];
                $role 			= Role::where('name', $role_name)->first();
                $employee_id 	= $row["employee_id"];
                $pg 			= $row["pg"];
                $user 				= new User;
                $user->username 	= $username;
                $user->name 		= $name;
                $user->email 		= $email;
                $user->phone 		= $phone;
                $user->role_id 		= $role->id;
                $user->employee_id 	= $employee_id;
                $user->area 		= $pg;
                $user->password 	= bcrypt('boomsprayer123');;
                $user->status 		= 'active';
                $user->save();
            }
            DB::commit();
        } catch(Exception $e){
            DB::rollback(); 
        }
    }
}
