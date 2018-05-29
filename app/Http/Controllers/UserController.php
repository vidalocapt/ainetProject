<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\User;
use Hash;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->only(['index', 'blockUser','unBlockUser', 'promoteUser','demoteUser']);
    }


    public function dashboard()
    {
        return view('dashboard');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagetitle = "List of Users";
        //Somente o campo nome preenchido

            if ($request->filled('name') && !$request->filled('type') && !$request->filled('status')){
                $users = User::where('name', 'like', "%{$request->query('name')}%")->get();
                return view('users.list', compact('users', 'pagetitle'));
            }
            //Somente o tipo preenchido
            if ($request->filled('type') && !$request->filled('name') && !$request->filled('status')){
                if ($request->query('type') == "admin"){
                    $users = User::where('admin', 1)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }

                if ($request->query('type') == "normal"){
                    $users = User::where('admin', 0)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }

            }

            //Somente o status preenchido
            if ($request->filled('status') && !$request->filled('type') && !$request->filled('name')){
                if ($request->query('status') == "blocked"){
                    $users = User::where('blocked', 1)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
                if ($request->query('status') == "unblocked"){
                    $users = User::where('blocked', 0)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }

            }

            //Nome e status preenchido
            if ($request->filled('name') && $request->filled('status') && !$request->filled('type')){
                if ($request->query('status') == "blocked"){
                    $users = User::where('blocked', 1)->where('name', 'like', "%{$request->query('name')}%")->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
                if ($request->query('status') == "unblocked"){
                    $users = User::where('blocked', 0)->where('name', 'like', "%{$request->query('name')}%")->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
            }

            //Tipo e status
            if(!$request->filled('name')&& $request->filled('type') && $request->query('type')=='admin' && $request->filled('status')){
                //admin e blocked
                if ($request->query('status') == "blocked"){
                    $users = User::where('admin', 1)->where('blocked',1)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
                //admin e unblocked
                if ($request->query('status') == "unblocked"){
                    $users = User::where('admin', 1)->where('blocked',0)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
            }

            if(!$request->filled('name')&& $request->filled('type') && $request->query('type')=='normal' && $request->filled('status')){
                //normal e unblocked
                if ($request->query('status') == "unblocked"){
                    $users = User::where('admin', 0)->where('blocked',0)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
                //normal e blocked
                if ($request->query('status') == "blocked"){
                    $users = User::where('admin', 0)->where('blocked',1)->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
            }
            //Nome e tipo preenchido
            if ($request->filled('name') && !$request->filled('status') && $request->filled('type')){
                if ($request->query('type') == "admin"){
                    $users = User::where('admin', 1)->where('name', 'like', "%{$request->query('name')}%")->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
                if ($request->query('type') == "normal"){
                    $users = User::where('admin', 0)->where('name', 'like', "%{$request->query('name')}%")->get();
                    return view('users.list', compact('users', 'pagetitle'));
                }
            }

            //Todos preenchidos
            if ($request->filled('name') && $request->filled('status') && $request->filled('type')){
                //nome + admin + blocked
                if($request->query('type')=='admin'){
                    if($request->query('status')=='blocked'){
                        $users = User::where('name','like','%'.$request->query('name').'%')->where('admin', 1)->where('blocked', 1)->get();
                        return view('users.list', compact('users', 'pagetitle'));
                    }
                }
                //nome + admin + unblocked
                if($request->query('type')=='admin'){
                    if($request->query('status')=='unblocked'){
                        $users = User::where('name','like','%'.$request->query('name').'%')->where('admin', 1)->where('blocked', 0)->get();
                        return view('users.list', compact('users', 'pagetitle'));
                    }
                }
                //nome + normal + blocked
                if($request->query('type')=='normal'){
                    if($request->query('status')=='blocked'){
                        $users = User::where('name','like','%'.$request->query('name').'%')->where('admin', 0)->where('blocked', 1)->get();
                        return view('users.list', compact('users', 'pagetitle'));
                    }
                }
                //nome + normal + unblocked
                if($request->query('type')=='normal'){
                    if($request->query('status')=='unblocked'){
                        $users = User::where('name','like','%'.$request->query('name').'%')->where('admin', 0)->where('blocked', 0)->get();
                        return view('users.list', compact('users', 'pagetitle'));
                    }
                }
            }else{
                $users = \App\User::all();
            }
            return view('users.list', compact('users', 'pagetitle'));
    }

    public function profiles(Request $request, User $user)
    {
        $pagetitle = "Profiles of Users";
        $name = $request->get('name');
        // Search for  a user based on their name.
        if ($request->has('name') ) {
            $users = $user->where('name', 'like', '%' . $name . '%')->get();
        }else{
            $users = \App\User::all();
        }

        $associates = DB::table ('associate_members') ->where('main_user_id', '=', Auth::id())->get();
        $associates_of = DB::table ('associate_members') ->where('associated_user_id', '=', Auth::id())->get();

        return view('profiles', compact('users','associates','associates_of', 'pagetitle'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = new User();
        return view('users.add', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        $user = new User;
        $user->fill($request->all());
        $user->password = Hash::make($request->password);

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'User added successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', [
            "user" => $user,

        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        $request = request();
        $user = Auth::user();
        $dados = $request->validate([
            'name' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
            'phone' => 'nullable|regex:/^[0-9 +\s]+$/',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_photo' => 'nullable|image',
        ]);

        $user->name = $dados['name'];
        $user->email = $dados['email'];
        $user->phone = $dados['phone'] ?? null;
        $file = $dados['profile_photo'] ?? null;

        if ($file != null) {

            $file_name = basename($file->store('profiles', 'public'));

            $user->update(['profile_photo' => $file_name]);
        }

        $user->save();
        return redirect()->route('dashboard', auth()->user()->id)->with('success', 'Profile has been Edited');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully');
    }

    public function blockUser(User $user)
    {
        $user->toggleBlock();
        $user->save();
        return redirect()->back();
    }

    public function unBlockUser(User $user)
    {
        $user->toggleBlock();
        $user->save();
        return redirect()->back();
    }

    public function promoteUser(User $user)
    {
        $user->toggleDemote();
        $user->save();
        return redirect()->back();
    }

    public function demoteUser(User $user)
    {
        $user->toggleDemote();
        $user->save();
        return redirect()->back();
    }

    public function filter(Request $request, User $user)
    {
        $name = $request->get('name');
        // Search for a user based on their name.
        if ($request->has('name')) {
            $users = $user->where('name', 'like', '%' . $name . '%')->get();
        }
        return view('profiles', compact('users'));
    }
}
