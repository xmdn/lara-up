<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\RegistersUsers as BaseRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use BaseRegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function baseRegister(Request $request)
    {
        // Validate the incoming request data
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            // If validation fails, return the validation errors
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create a new user using the 'create' method
        $user = $this->create($request->all());

        // If the user instance is null (registration failed), return an error response
        if (!$user) {
            return response()->json(['error' => 'Registration failed'], 500);
        }

        // Perform any additional actions after successful registration
        $this->registered($request, $user);

        // Return a success response
        return response()->json(['status' => 'Registration successful', 'user' => $user]);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    protected function registered(Request $request, User $user)
    {
        if ($user instanceof MustVerifyEmail) {
            return response()->json(['status' => trans('verification.sent')]);
        }

        return response()->json($user);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if ($data['role'] === 'Lecturer') {
            return Validator::make($data, [
                'first_name' => 'required|max:255',
                'last_name' => 'max:255',
                'role' => 'required|max:255|in:Student,Lecturer',
                'email' => 'required|email|max:255|unique:users|regex:/^[a-zA-Z0-9.]+@(?!.*(student)).*.ac.id.*$/',
                'password' => 'required|min:8',
            ], [
                'email.regex' => 'You were using student academic email address or not using an academic email at all.'
            ]);
        } else {
            return Validator::make($data, [
                'first_name' => 'required|max:255',
                'last_name' => 'max:255',
                'role' => 'required|max:255|in:Student,Lecturer',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:8',
            ]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role' => $data['role'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
