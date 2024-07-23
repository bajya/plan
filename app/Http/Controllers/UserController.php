<?php
    
namespace App\Http\Controllers;
    
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use URL;
use Illuminate\Support\Arr;
    
class UserController extends Controller
{
    public $user;
    public $columns;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->user = new User;
        $this->columns = [
            "select", "name", "email", "created_at", "status", "activate", "action",
        ];
        $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index','store']]);
        $this->middleware('permission:user-create', ['only' => ['create','store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request) {
        return view('backend.users.index');
    }

    public function usersAjax(Request $request) {
        //dd($request->status);
        if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->user->fetchUsers($request, $this->columns);
        $total = $records->count();
        if (isset($request->start)) {
            $users = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $users = $records->offset($request->start)->limit(count($total))->get();
            //$users = $records->offset(0)->limit(2)->get();
        }
        $result = [];
        foreach ($users as $user) {
            $data = [];
            $data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $user->id . '"><i class="input-helper"></i></label></div>';
            $data['name'] = ucfirst($user->name);
            $data['email'] = $user->phone_code.' '.$user->mobile;
            $data['image'] = ($user->avatar != null) ? '<img src="'.URL::asset('/img/avatars/' . $user->avatar).'" width="70" />' : '-';
            $data['status'] = ucfirst(config('constants.STATUS.' . $user->status));
            $data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($user->status == 'active' ? ' checked' : '') . ' data-id="' . $user->id . '" data-code="' . $user->name . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusUsers"></div></div>';
            $action = '';
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editUsers', ['id' => $user->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
            
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewUsers', ['id' => $user->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('usersFavProd', ['id' => $user->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Fav Product"><i class="fa fa-eye"></i></a>';

            /*$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteUsers" data-toggle="tooltip" data-id="' . $user->id . '" data-placement="bottom" title="Delete"><i class="fa fa-times"></i></a>';*/


           
            $data['action'] = $action;

            $result[] = $data;
        }
        $data = json_encode([
            'data' => $result,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
        ]);
        echo $data;

    }
    public function create() {
        $type = 'add';
        $url = route('addUsers');
        $roles = Role::pluck('name','name')->all();
        return view('backend.users.create', compact('type', 'url', 'roles'));
    }

    public function checkUsers(Request $request, $id = null) {
        if (isset($request->mobile)) {
            $check = User::where('mobile', $request->mobile);
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
            'user_image' => 'required|mimes:jpeg,png,jpg,gif,svg',
           // 'email' => 'required|email|unique:users,email',
            'mobile' => 'required|min:8|numeric|unique:users,mobile',
           // 'password' => 'required|same:confirm-password',
            'roles' => 'required'

        ]);

        $attr = [
            'name' => 'Name',
            'user_image' => 'Image',
           // 'email' => 'Email',
            'mobile' => 'Mobile',
           // 'password' => 'Password',
            'roles' => 'Roles'
        ];

        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
            return redirect()->route('createUsers')->withInput($request->all())->withErrors($validate);
        } else {
            try {
                $user = new User;
                $filename = "";
                if ($request->hasfile('user_image')) {
                    $file = $request->file('user_image');
                    $filename = time() . $file->getClientOriginalName();
                    $filename = str_replace(' ', '', $filename);
                    $filename = str_replace('.jpeg', '.jpg', $filename);
                    $file->move(public_path('img/avatars'), $filename);
                }
                if ($filename != "") {
                    $user->avatar = $filename;
                }
                $user->name = ucfirst($request->name);
                //$user->email = $request->email;
                $user->mobile = $request->mobile;
                $user->password = Hash::make($request->mobile);
                $user->status = $request->status;
                $user->is_admin = 'No';
                $user->created_at = date('Y-m-d H:i:s');
                $user->updated_at = date('Y-m-d H:i:s');
                if ($user->save()) {
                    $user->assignRole($request->post('roles'));
                    $request->session()->flash('success', 'User added successfully');
                    return redirect()->route('users');
                } else {
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('users');
                }
            } catch (Exception $e) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('users');
            }

        }
    }
    public function show(Request $request, $id = null) {
        $type = 'View';
        if (isset($id) && $id != null) {
            $user = User::where('id', $id)->first();
            if (isset($user->id)) {
                return view('backend.users.view', compact('user', 'type'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('users');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('users');
        }
    }
    public function edit(Request $request, $id = null) {
        if (isset($id) && $id != null) {
            $user = User::where('id', $id)->first();
            if (isset($user->id)) {
                $type = 'edit';
                $url = route('updateUsers', ['id' => $user->id]);
                $roles = Role::pluck('name','name')->all();
                $userRole = $user->roles->pluck('name','name')->all();
                return view('backend.users.create', compact('user', 'type', 'url', 'roles', 'userRole'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('users');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('users');
        }
    }
    public function update(Request $request, $id = null) {
        if (isset($id) && $id != null) {
            $user = User::where('id', $id)->first();
            if (isset($user->id)) {
                $validate = Validator($request->all(), [
                    'name' => 'required',
                    //'email' => 'required|email|unique:users,email,'.$id,
                    'mobile' => 'required|min:8|numeric|unique:users,mobile,'.$id,
                    //'mobile' => 'same:confirm-password',
                    'roles' => 'required'

                ]);
                $attr = [
                    'name' => 'First Name',
                    //'email' => 'Email',
                    'mobile' => 'Mobile',
                    //'password' => 'Password',
                    'roles' => 'Roles'
                ];

                $validate->setAttributeNames($attr);

                if ($validate->fails()) {
                    return redirect()->route('editUsers', ['id' => $user->id])->withInput($request->all())->withErrors($validate);
                } else {
                    try {
                        $filename = "";
                        if ($request->hasfile('user_image')) {
                            $file = $request->file('user_image');
                            $filename = time() . $file->getClientOriginalName();
                            $filename = str_replace(' ', '', $filename);
                            $filename = str_replace('.jpeg', '.jpg', $filename);
                            $file->move(public_path('img/avatars'), $filename);
                            if ($user->avatar != null && file_exists(public_path('img/avatars/' . $user->avatar))) {
                                if ($user->avatar != 'noimage.jpg') {
                                    unlink(public_path('img/avatars/' . $user->avatar));
                                }
                            }
                        }
                        if ($filename != "") {
                            $user->avatar = $filename;
                        }
                        $user->name = $request->post('name');
                        //$user->email = $request->email;
                        $user->mobile = $request->mobile;
                        $user->status = $request->status;
                        $user->is_admin = 'No';
                        $user->updated_at = date('Y-m-d H:i:s');
                        if (isset($request->mobile) && !empty($request->mobile)) {
                            $user->password = Hash::make($request->mobile);
                        }
                        if ($user->save()) {
                             DB::table('model_has_roles')->where('model_id',$id)->delete();
                            $user->assignRole($request->post('roles'));
                            $request->session()->flash('success', 'User updated successfully');
                            return redirect()->route('users');
                        } else {
                            $request->session()->flash('error', 'Something went wrong. Please try again later.');
                            return redirect()->route('users');
                        }
                    } catch (Exception $e) {
                        $request->session()->flash('error', 'Something went wrong. Please try again later.');
                        return redirect()->route('users');
                    }

                }
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('users');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('users');
        }

    }
    // activate/deactivate user
    public function updateStatus(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $user = User::find($request->statusid);

            if (isset($user->id)) {
                $user->status = $request->status;
                if ($user->save()) {
                    $request->session()->flash('success', 'User updated successfully.');
                    return redirect()->back();
                } else {
                    $request->session()->flash('error', 'Unable to update user. Please try again later.');
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

    // activate/deactivate user
    public function updateStatusAjax(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $user = User::find($request->statusid);

            if (isset($user->id)) {
                $user->status = $request->status;
                if ($user->save()) {
                    echo json_encode(['status' => 1, 'message' => 'User updated successfully.']);
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Unable to update user. Please try again later.']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid user']);
            }
        } else {
            echo json_encode(['status' => 0, 'message' => 'Invalid user']);
        }

    }
    public function destroy(Request $request) {
        if (isset($request->deleteid) && $request->deleteid != null) {
            $user = User::find($request->deleteid);

            if (isset($user->id)) {
                $user->status = 'delete';
                if ($user->save()) {
                    DB::table('model_has_roles')->where('model_id',$user->id)->delete();
                    echo json_encode(["status" => 1, 'ids' => json_encode($request->deleteid), 'message' => 'Users deleted successfully.']);
                } else {
                    echo json_encode(["status" => 0, 'message' => 'Not all users were deleted. Please try again later.']);
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
                $user = User::find($id);

                if (isset($user->id)) {
                    $user->status = 'delete';
                    if ($user->save()) {
                        DB::table('model_has_roles')->where('model_id',$user->id)->delete();
                        $count++;
                    }
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Users deleted successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all users were deleted. Please try again later.']);
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
                $user = User::find($id);

                if (isset($user->id)) {
                    if ($user->status == 'active') {
                        $user->status = 'inactive';
                    } elseif ($user->status == 'inactive') {
                        $user->status = 'active';
                    }

                    if ($user->save()) {
                        $count++;
                    }
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Users updated successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all users were updated. Please try again later.']);
            }
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }

    public function usersFavProd(Request $request) {
        return view('backend.users.fav_product');
    }

    public function usersFavProdAjax(Request $request) {
        //dd($request->status);
        if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->user->fetchUsersFavProd($request, $this->columns);
        $total = $records->get();
        if (isset($request->start)) {
            $lists = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $lists = $records->offset($request->start)->limit(count($total))->get();
            //$users = $records->offset(0)->limit(2)->get();
        }
        $result = [];

        foreach ($lists as $key=>$res) {
            $data = [];
            $data['sno'] = $key + 1;
            $data['name'] = ucfirst($res['product']->name);
            $data['image'] = ($res['product']->image_url != null) ? '<img src="'.$res['product']->image_url.'" width="70" />' : '-';
            $pdate = '-';
            if ($res->is_user_status == 'active') {
                $status = 'On';
            }else if ($res->is_user_status == 'active') {
                $status = 'Off';
            }else{
                $status = 'Pause';
                $pdate = $res->pause_expire_time;
            }
            $data['status'] = $status;
            $data['pause'] = $pdate;
            $action = '';
            
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewProduct', ['id' => $res['product']->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';


           
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


}