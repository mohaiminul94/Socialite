<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Socialite;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the facebook authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from facebook.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request)
    {
        if (!$request->has('code') || $request->has('denied')) {
            return redirect('/');
        }
        $socialiteUser = Socialite::driver('facebook')->user();
        if($socialiteUser->email) {
            $findUser= User::where('email',$socialiteUser->email)->first();
            if($findUser) {
                Auth::login($findUser);
                return redirect('home');
            } else {
                $user = new User;
                $user->name               = $socialiteUser->name;
                $user->email              = $socialiteUser->email;
                $user->profile_picture    = $socialiteUser->avatar_original;
                $user->remember_token     = $socialiteUser->token;
                $user->email_verified_at  = Carbon::now();
                $user->save();
                Auth::login($user);

                return redirect('home');
            }
        } else {
            return "Email not found";
        }

    }

}
