<?php
    
namespace App\Http\Controllers;

use App\Library\Helper;
use App\Library\Notify;  
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
    
class RoleController extends Controller
{
    public $role;
    public $permission;
    public $columns;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->role = new Role;
        $this->permission = new Permission;
        $this->columns = [
            "select", "name", "status", "activate", "action",
        ];
         $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
         $this->middleware('permission:role-create', ['only' => ['create','store']]);
         $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /*$roles = Role::orderBy('id','DESC')->whereNotIn('id', [1,2])->paginate(500);
        return view('backend.roles.index',compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);*/
        return view('backend.roles.index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function roleAjax(Request $request) {

        $request->search = $request->search['value'];
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->fetchRoles($request, $this->columns);
        $total = $records->get();
        if (isset($request->start)) {
            $roles = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $roles = $records->offset($request->start)->limit(count($total))->get();
        }
        $result = [];
        foreach ($roles as $role) {
            $data = [];
            $data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $role->id . '"><i class="input-helper"></i></label></div>';
            $data['name'] = $role->name;
            $data['status'] = ucfirst(config('constants.STATUS.' . $role->status));
            $data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($role->status == 'active' ? ' checked' : '') . ' data-id="' . $role->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusRole"></div></div>';
            $action = '';
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editRole', ['id' => $role->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
            
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewRole', ['id' => $role->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
            $action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteRole" data-toggle="tooltip" data-id="' . $role->id . '" data-placement="bottom" title="Delete"><i class="fa fa-times"></i></a>';
            $data['action'] = $action;
            $result[] = $data;
        }
        $data = json_encode([
            'data' => $result,
            'recordsTotal' => count($total),
            'recordsFiltered' => count($total),
        ]);
        echo $data;
    }    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /*$permission = Permission::get();
        return view('backend.roles.create',compact('permission'));*/

        $type = 'add';
        $url = route('addRole');
        $role = new Role;
        $permission = Permission::get();
        return view('backend.roles.create', compact('type', 'url', 'role', 'permission'));
    }
    /**
     * check for unique name during adding role
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkRole(Request $request, $id = null) {
        if (isset($request->role_name)) {
            $check = Role::where('name', $request->role_name);
            if (isset($id) && $id != null) {
                $check = $check->where('id', '!=', $id);
            }
            $check = $check->where('status', '!=', 'DL')->count();
            if ($check > 0) {
                return "false";
            } else {
                return "true";
            }
        } else {
            return "true";
        }
    }    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       /* $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);
    
        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));
    
        return redirect()->route('roles.index')
                        ->with('success','Role created successfully');*/

        $validate = Validator($request->all(), [
            'role_name' => 'required',
            'permission.*' => 'required',

        ]);

        $attr = [
            'role_name' => 'Name',
            'permission.*' => 'Operation',
        ];

        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
            return redirect()->route('createRole')->withInput($request->all())->withErrors($validate);
        } else {
            try {
                $role = new Role;

                $role->name = ucfirst($request->role_name);
                $role->status = $request->status;
                $role->created_at = date('Y-m-d H:i:s');
                $role->updated_at = date('Y-m-d H:i:s');

                if ($role->save()) {
                    //add module permissions to this role
                    $role->syncPermissions($request->input('permission'));

                    $request->session()->flash('success', 'Role added successfully');
                    return redirect()->route('roles');
                } else {
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('roles');
                }
            } catch (Exception $e) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('roles');
            }

        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id = null)
    {
        /*$role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();
    
        return view('backend.roles.show',compact('role','rolePermissions'));*/
        if (isset($id) && $id != null) {
            $role = Role::find($id);
            // dd($role);

            if (isset($role->id)) {
                $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();

                return view('backend.roles.show', compact('role', 'rolePermissions'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('roles');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('roles');
        }
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id = null)
    {
        /*$role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
    
        return view('backend.roles.edit',compact('role','permission','rolePermissions'));*/

        if (isset($id) && $id != null) {
            $role = Role::find($id);

            if (isset($role->id)) {
                $type = 'edit';
                $url = route('updateRole', ['id' => $role->id]);
                $permission = Permission::get();
                $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
                return view('backend.roles.create', compact('role', 'type', 'url', 'permission', 'rolePermissions'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('roles');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('roles');
        }
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = null)
    {
        /*$this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
    
        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();
    
        $role->syncPermissions($request->input('permission'));
    
        return redirect()->route('roles.index')
                        ->with('success','Role updated successfully');*/

        if (isset($id) && $id != null) {
            $role = Role::where('id', $id)->first();
            if (isset($role->id)) {
                $validate = Validator($request->all(), [
                    'role_name' => 'required',
                    'permission.*' => 'required',

                ]);

                $attr = [
                    'role_name' => 'Name',
                    'permission*' => 'Operation',
                ];

                $validate->setAttributeNames($attr);

                if ($validate->fails()) {
                    return redirect()->route('editRole', ['id' => $role->id])->withInput($request->all())->withErrors($validate);
                } else {
                    try {
                        $role->name = ucfirst($request->role_name);
                        $role->status = $request->status;
                        $role->created_at = date('Y-m-d H:i:s');
                        $role->updated_at = date('Y-m-d H:i:s');

                        if ($role->save()) {
                            //add module permissions to this role
                            $role->syncPermissions($request->input('permission'));

                            $request->session()->flash('success', 'Role updated successfully');
                            return redirect()->route('roles');
                        } else {
                            $request->session()->flash('error', 'Something went wrong. Please try again later.');
                            return redirect()->route('roles');
                        }
                    } catch (Exception $e) {
                        $request->session()->flash('error', 'Something went wrong. Please try again later.');
                        return redirect()->route('roles');
                    }

                }
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('roles');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('roles');
        }
    }
    // activate/deactivate role
    public function updateStatus(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $role = Role::find($request->statusid);

            if (isset($role->id)) {
                $role->status = $request->status;
                if ($role->save()) {
                    $request->session()->flash('success', 'Role updated successfully.');
                    return redirect()->back();
                } else {
                    $request->session()->flash('error', 'Unable to update role. Please try again later.');
                    return redirect()->back();
                }

            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->back();
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->back();
        }

    }

    // activate/deactivate role
    public function updateStatusAjax(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $role = Role::find($request->statusid);

            if (isset($role->id)) {
                $role->status = $request->status;
                if ($role->save()) {
                    echo json_encode(['status' => 1, 'message' => 'Role updated successfully.']);
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Unable to update role. Please try again later.']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid Role']);
            }
        } else {
            echo json_encode(['status' => 0, 'message' => 'Invalid Role']);
        }

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        /*if (auth()->user()->id == 1 || $id == 2) {
            return redirect()->back()->with('success','You can not delete this role!');
        }else{
            DB::table("roles")->where('id',$id)->delete();
            return redirect()->route('roles.index')
                        ->with('success','Role deleted successfully');
        }*/
        if (isset($request->deleteid) && $request->deleteid != null) {
            if ($request->deleteid == 1 || $request->deleteid == 2) {
                return redirect()->back()->with('success','You can not delete this role!');
            }else{
                $role = Role::find($request->deleteid);

                if (isset($role->id)) {
                    $role->status = 'delete';
                    if ($role->save()) {
                        DB::table("roles")->where('id',$role->id)->delete();
                        $request->session()->flash('success', 'User deleted successfully.');
                        return redirect()->back();
                    } else {
                        $request->session()->flash('error', 'Unable to delete role. Please try again later.');
                        return redirect()->back();
                    }

                } else {
                    $request->session()->flash('error', 'Invalid Data');
                    return redirect()->back();
                }

               
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->back();
        }
    }
    /**
     * Remove multiple resource from storage.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function bulkdelete(Request $request) {

        if (isset($request->ids) && $request->ids != null) {
            $ids = count($request->ids);
            $count = 0;
            foreach ($request->ids as $id) {
                $role = Role::find($id);
                if (isset($role->id)) {
                    if ($request->ids != 1 || $request->ids != 2) {
                        $role->status = 'delete';
                        if ($role->save()) {
                            DB::table("roles")->where('id',$role->id)->delete();
                            $count++;
                        }
                    }
                    
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Roles deleted successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all roles were deleted. Please try again later.']);
            }
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }
    /**
     * activate/deactivate multiple resource from storage.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function bulkchangeStatus(Request $request) {

        if (isset($request->ids) && $request->ids != null) {
            $ids = count($request->ids);
            $count = 0;
            foreach ($request->ids as $id) {
                $role = Role::find($id);
                if (isset($role->id)) {
                    if ($role->status == 'active') {
                        $role->status = 'inactive';
                    } elseif ($role->status == 'inactive') {
                        $role->status = 'active';
                    }
                    if ($role->save()) {
                        $count++;
                    }
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Roles updated successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all roles were updated. Plese try again later.']);
            }
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }
    public function fetchRoles($request, $columns) {
        $query = Role::where('name', '!=', 'Admin')->where('name', '!=', 'User')->where('status', '!=', 'delete');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        if (isset($request->order_column)) {
            $roles = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $roles = $query->orderBy('created_at', 'desc');
        }
        return $roles;
    }


}