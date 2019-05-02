<?php

namespace App\Http\Controllers\API;

use App\User;
use Faker\Provider\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $this->authorize('isAdmin');
        if (Gate::allows('isAdmin') || Gate::allows('isAuthor')) {

            return User::when($request->search, function($query, $search) {
                    $query->where('name', 'like', "%$search%")
                          ->orWhere('email', 'like', "%$search%")
                          ->orWhere('type', $search);
                })
                ->latest()
                ->paginate(3);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
           'name' => 'required|string|min:3|max:191',
           'email' => 'required|string|email|max:191|unique:users',
           'password' => 'required|string|min:8|max:191'
        ]);
        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'type' => $request['type']
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return void
     */
    public function show($id)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return auth('api')->user();
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $this->validate($request, [
            'name' => 'required|string|min:3|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|max:191'
        ]);

        if ($request->photo != $user->photo) {
            preg_match('/(?<=\/).*?(?=;)/', $request->photo, $matches);
            $name = time() . \Str::random(10) . '.' . $matches[0];

            \Image::make($request->photo)->save(public_path('img/profile/') . $name);

            $request->merge(['photo' => $name]);

            if($user->photo) {
               $old_photo_path = public_path('img/profile/') . $user->photo;
               if(file_exists($old_photo_path))
                    unlink($old_photo_path);
            }
        }
        
        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
            'photo' => $request->photo
        ]);
        if ($request->password)
            $user->password = Hash::make($request->password);

        $user->update();

        return ['message' => 'user updated successfully'];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|min:3|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8|max:191'
        ]);
        $user = User::find($id);
        if (!$user) return ['message' => 'user not found'];

        tap($user, function($user) use($request) {
            $user->name = $request['name'];
            $user->email = $request['email'];
            $user->type = $request['type'];
            if ($request['password'])
                $user->password =  Hash::make($request['password']);
            $user->save();
        });

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return array
     */
    public function destroy($id)
    {
        $this->authorize('isAdmin');
        
        if (User::destroy($id))
            return ['message' => 'User Deleted'];
    }
}
