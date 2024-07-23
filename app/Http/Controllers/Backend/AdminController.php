<?php
    
namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;
    
class AdminController extends Controller
{
    public $admin;
    public $columns;

    public function __construct() {
        $this->admin = new User;
        $this->columns = [
            "select", "name", "email", "created_at", "status", "activate", "action",
        ];
        $this->middleware('permission:admin-list|admin-create|admin-edit|admin-delete', ['only' => ['index','store']]);
         $this->middleware('permission:admin-create', ['only' => ['create','store']]);
         $this->middleware('permission:admin-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:admin-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request) {
        return view('backend.admins.index');
    }

    public function adminsAjax(Request $request) {
        if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
        
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->admin->fetchAdmins($request, $this->columns);
        $total = $records->get();
        if (isset($request->start)) {
            $admins = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $admins = $records->offset($request->start)->limit(count($total))->get();
            //$admins = $records->offset(0)->limit(2)->get();
        }
        $result = [];
        foreach ($admins as $admin) {
            $data = [];
            $data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $admin->id . '"><i class="input-helper"></i></label></div>';
            $data['name'] = ucfirst($admin->name);
            $data['email'] = $admin->email;
            $role = '';
            if(!empty($admin->getRoleNames())){
                foreach($admin->getRoleNames() as $v){
                   $role.='<label class="badge badge-success">'.$v.'</label>';
                }
            }
            $data['role'] = $role;
            $data['status'] = ucfirst(config('constants.STATUS.' . $admin->status));
            $data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($admin->status == 'active' ? ' checked' : '') . ' data-id="' . $admin->id . '" data-code="' . $admin->name . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusAdmins"></div></div>';
            $action = '';
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editAdmins', ['id' => $admin->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
            
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewAdmins', ['id' => $admin->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';

            $action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteAdmins" data-toggle="tooltip" data-id="' . $admin->id . '" data-placement="bottom" title="Delete"><i class="fa fa-times"></i></a>';


           
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
    public function create() {
        $type = 'add';
        $url = route('addAdmins');
        $roles = Role::pluck('name','name')->all();
        return view('backend.admins.create', compact('type', 'url', 'roles'));
    }

    public function checkAdmins(Request $request, $id = null) {
        if (isset($request->email)) {
            $check = User::where('email', $request->email);
            if (isset($id) && $id != null) {
                $check = $check->where('id', '!=', $id);
            }
            $check = $check->where('status', '!=', 'delete')->count();
            if ($check > 0) {
                return "false";
            } else {
                return "true";
            }
        } else {
            return "true";
        }
    }
    public function store(Request $request) {
        $validate = Validator($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'

        ]);

        $attr = [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'roles' => 'Roles'
        ];

        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
            return redirect()->route('createAdmins')->withInput($request->all())->withErrors($validate);
        } else {
            try {
                $admin = new User;
                $admin->name = ucfirst($request->name);
                $admin->email = $request->email;
                $admin->password = Hash::make($request->password);
                $admin->status = $request->status;
                $admin->is_admin = 'Yes';
                $admin->created_at = date('Y-m-d H:i:s');
                $admin->updated_at = date('Y-m-d H:i:s');
                if ($admin->save()) {
                    $admin->assignRole($request->post('roles'));
                    $request->session()->flash('success', 'Admin added successfully');
                    return redirect()->route('admins');
                } else {
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('admins');
                }
            } catch (Exception $e) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('admins');
            }

        }
    }
    public function show(Request $request, $id = null) {
        $type = 'View';
        if (isset($id) && $id != null) {
            $admin = User::where('id', $id)->first();
            if (isset($admin->id)) {
                return view('backend.admins.view', compact('admin', 'type'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('admins');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('admins');
        }
    }
    public function edit(Request $request, $id = null) {
        if (isset($id) && $id != null) {
            $admin = User::where('id', $id)->first();
            if (isset($admin->id)) {
                $type = 'edit';
                $url = route('updateAdmins', ['id' => $admin->id]);
                $roles = Role::pluck('name','name')->all();
                $adminRole = $admin->roles->pluck('name','name')->all();
                return view('backend.admins.create', compact('admin', 'type', 'url', 'roles', 'adminRole'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('admins');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('admins');
        }
    }
    public function update(Request $request, $id = null) {
        if (isset($id) && $id != null) {
            $admin = User::where('id', $id)->first();
            if (isset($admin->id)) {
                $validate = Validator($request->all(), [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email,'.$id,
                    'password' => 'same:confirm-password',
                    'roles' => 'required'

                ]);
                $attr = [
                    'name' => 'First Name',
                    'email' => 'Email',
                    'password' => 'Password',
                    'roles' => 'Roles'
                ];

                $validate->setAttributeNames($attr);

                if ($validate->fails()) {
                    return redirect()->route('editAdmins', ['id' => $admin->id])->withInput($request->all())->withErrors($validate);
                } else {
                    try {
                        $admin->name = $request->post('name');
                        $admin->email = $request->email;
                        $admin->status = $request->status;
                        $admin->is_admin = 'Yes';
                        $admin->updated_at = date('Y-m-d H:i:s');
                        if (isset($request->password) && !empty($request->password)) {
                            $admin->password = Hash::make($request->password);
                        }
                        if ($admin->save()) {
                             DB::table('model_has_roles')->where('model_id',$id)->delete();
                            $admin->assignRole($request->post('roles'));
                            $request->session()->flash('success', 'Admin updated successfully');
                            return redirect()->route('admins');
                        } else {
                            $request->session()->flash('error', 'Something went wrong. Please try again later.');
                            return redirect()->route('admins');
                        }
                    } catch (Exception $e) {
                        $request->session()->flash('error', 'Something went wrong. Please try again later.');
                        return redirect()->route('admins');
                    }

                }
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('admins');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('admins');
        }

    }
    // activate/deactivate admin
    public function updateStatus(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $admin = User::find($request->statusid);

            if (isset($admin->id)) {
                $admin->status = $request->status;
                if ($admin->save()) {
                    $request->session()->flash('success', 'Admin updated successfully.');
                    return redirect()->back();
                } else {
                    $request->session()->flash('error', 'Unable to update admin. Please try again later.');
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

    // activate/deactivate admin
    public function updateStatusAjax(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $admin = User::find($request->statusid);

            if (isset($admin->id)) {
                $admin->status = $request->status;
                if ($admin->save()) {
                    echo json_encode(['status' => 1, 'message' => 'Admin updated successfully.']);
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Unable to update admin. Please try again later.']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid admin']);
            }
        } else {
            echo json_encode(['status' => 0, 'message' => 'Invalid admin']);
        }

    }
    public function destroy(Request $request) {
        if (isset($request->deleteid) && $request->deleteid != null) {
            $admin = User::find($request->deleteid);

            if (isset($admin->id)) {
                $admin->status = 'delete';
                if ($admin->save()) {
                    DB::table('model_has_roles')->where('model_id',$admin->id)->delete();
                    echo json_encode(["status" => 1, 'ids' => json_encode($request->deleteid), 'message' => 'Admins deleted successfully.']);
                } else {
                    echo json_encode(["status" => 0, 'message' => 'Not all admins were deleted. Please try again later.']);
                }
            } else {
                echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
            }
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }
    public function bulkdelete(Request $request) {

        if (isset($request->ids) && $request->ids != null) {
            $ids = count($request->ids);
            $count = 0;
            foreach ($request->ids as $id) {
                $admin = User::find($id);

                if (isset($admin->id)) {
                    $admin->status = 'delete';
                    if ($admin->save()) {
                        DB::table('model_has_roles')->where('model_id',$admin->id)->delete();
                        $count++;
                    }
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Admins deleted successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all admins were deleted. Please try again later.']);
            }
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }
    public function bulkchangeStatus(Request $request) {

        if (isset($request->ids) && $request->ids != null) {
            $ids = count($request->ids);
            $count = 0;
            foreach ($request->ids as $id) {
                $admin = User::find($id);

                if (isset($admin->id)) {
                    if ($admin->status == 'active') {
                        $admin->status = 'inactive';
                    } elseif ($admin->status == 'inactive') {
                        $admin->status = 'active';
                    }

                    if ($admin->save()) {
                        $count++;
                    }
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Admins updated successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all admins were updated. Please try again later.']);
            }
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }


}