<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Model\Menu;
use App\Model\Permission;
use App\Model\RolePermission;

class ProfileComposer
{
    protected $user;

    public function __construct() {
        $this->user = Auth::user();
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view) {
        $path = Request()->path();
        $profile = $this->user;
        $profile->image = !empty($profile->avatar_thumb)?$profile->avatar_thumb:'/img/user.png';
        $res = RolePermission::join('permissions as p', 'p.id', '=', 'role_permission.permission_id')
            ->where('role_permission.role_id', $this->user->role_id)
            ->get(['p.code']);
        $permissions = ['*'];
        foreach($res as $o){
            $permissions[] = $o->code;
        }
        $exclude = [];
        $res = Menu::whereIn('permission_code', $permissions)
            ->whereNotIn('code', $exclude)
            ->orderBy('sort_order', 'ASC')
            ->get(['id','code','label','path','extra_paths','icon', 'target', 'parent_id']);
        $data1 = [];
        $data2 = [];
        $active_menu_id = 0;
        $arr_path = explode("/", $path);
        $arr_path_count = count($arr_path);
        $x = [];
        foreach($res as $o){
            $data1[$o->id] = $o;
            $data2[$o->parent_id][] = $o;
            if($o->path != "#"){
                $arr_menu_paths = [ $o->path ];
                if(!empty($o->extra_paths)){
                    $arr_extra_paths = explode(",", $o->extra_paths);
                    foreach($arr_extra_paths as $v){
                        if(!empty($v)){
                            $arr_menu_paths[] = $o->path.'/'.$v;
                        }
                    }
                }
                foreach($arr_menu_paths as $k=>$v){
                    $arr_v = explode("/", $v);
                    if($arr_path_count == count($arr_v)){
                        $arr_path1 = $arr_path;
                        foreach($arr_v as $k2=>$v2){
                            if($v2=="*"){
                                $arr_path1[$k2] = "*";
                            }
                        }
                        if(implode("/",$arr_path1) == $v){
                            $active_menu_id = $o->id;
                        }
                    }
                }
            }
        }
        $active_menu = $this->getActiveMenu($data1, $active_menu_id);
        $menu = $this->buildMenu($data2, $active_menu);

        $view->with([
            'profile'           => $profile,
            'active_menu'       => $active_menu,
            'menu'              => $menu
        ]);
    }

    function buildMenu($data, $active_menu, $parent = 0){
        if (isset($data[$parent])) {
            if($parent == 0){
                $html = "";
            } else {
                $html = "<ul class='treeview-menu'>";
            }
            foreach ($data[$parent] as $v) {
                $child = $this->buildMenu($data, $active_menu, $v->id);
                $active_label = in_array($v->code, $active_menu)?'active':'';
                if ($child) {
                    $html .= '<li class="treeview '.$active_label.'">';
                    $html .= '<a href="#">';
                    $html .= '<i class="'.$v->icon.'"></i><span>'.$v->label.'</span>';
                    $html .= ' <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>';
                    $html .= '</a>';
                    $html .= $child;
                    $html .= '</li>';
                } else {
                    $html .= '<li class="'.$active_label.'">';
                    $html .= '<a href="'.url($v->path).'" target="'.$v->target.'">';
                    $html .= '<i class="'.$v->icon.'"></i><span>'.$v->label.'</span>';
                    $html .= '</a>';
                    $html .= '</li>';
                }
            }
            if($parent == 0){
                $html .= "";
            } else {
                $html .= "</ul>";
            }
            
            return $html;
        } else {
            return false;
        }
    }

    private function getActiveMenu($data, $id){
        $active_menu = [];
        if (isset($data[$id])) {
            $active_menu[] = $data[$id]->code;
            if(!empty($data[$id]->parent_id)){
                $active_menu = array_merge($active_menu, $this->getActiveMenu($data, $data[$id]->parent_id));
            }
        }
        return $active_menu;
    }
}