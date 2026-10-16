<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role_or_permission:super_admin')->except(['myprofile', 'updateProfile']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): View
    {

        //create default roles
        try{
            $role = Role::findByName('staff');
        }
        catch(Exception $e){
            Role::create(['name' => 'staff']);
        }
        try{
            $role = Role::findByName('customer');
        }
        catch(Exception $e){
            Role::create(['name' => 'customer']);
        }

        $data = User::latest()->paginate(50);

        return view('users.index', compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 50);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $roles = Role::pluck('name', 'name')->all();

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'same:confirm-password',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'roles' => 'required'
        ]);

        $input = $request->only(['name', 'email', 'password']);
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->syncRoles($request->input('roles'));

        if ($request->ajax()) {
            return response()->json(['success'=> 'success', 'message' => 'User created successfully']);
        }

        return redirect()->route('users.index')
            ->with('success', 'User created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): View
    {
        $user = User::findOrFail($id);

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        $user = User::findOrFail($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        if(empty($userRole)){
            $userRole['staff'] = 'staff';
        }
        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    public function myprofile(){
        $userId = auth()->id();
        $user = User::findOrFail($userId);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.profile', compact('user', 'roles', 'userRole'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ];

        if ($request->filled('password')) {
            $rules['current_password'] = 'required|current_password';
            $rules['password'] = [
                'required',
                'same:confirm-password',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ];
        }

        $request->validate($rules);

        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return redirect()->route('users.myprofile')->with('success', 'Profile updated successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => [
                'nullable',
                'same:confirm-password',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'roles' => 'nullable|array'
        ]);

        $user = User::findOrFail($id);
        $input = $request->only(['name', 'email']);

        if ($request->filled('password')) {
            $input['password'] = Hash::make($request->input('password'));
        }

        $user->update($input);
        
        if ($request->has('roles')) {
            $user->syncRoles($request->input('roles', []));
        }

        if ($request->ajax()) {
            return response()->json(['success'=> 'success', 'message' => 'User updated successfully']);
        }

        return redirect()->back()->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): RedirectResponse
    {
        if ((int)$id === (int)auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account');
        }

        $user = User::findOrFail($id);
        if ($user->hasRole('super_admin')) {
            $superAdminCount = User::role('super_admin')->count();
            if ($superAdminCount <= 1) {
                return redirect()->route('users.index')->with('error', 'Cannot delete the last super_admin account');
            }
        }

        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully');
    }
}
