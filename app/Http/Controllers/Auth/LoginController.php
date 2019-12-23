<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Socialite;

use App\Http\Requests\ValidateSecretRequest;
use Cache;
use Illuminate\Contracts\Auth\Authenticatable;


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
	protected $redirectTo = '/courier';

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
	 * Redirect the user to the Social media authentication page.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function redirectToProvider($service)
	{
		return Socialite::driver($service)->redirect();
	}

	/**
	 * Obtain the user information from Social media.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function handleProviderCallback($service)
	{
		$userSocialite = Socialite::driver($service)->user();
		$findUser = User::where('email', $userSocialite->email)->first();
		if ($findUser) {
			// return $findUser;
			// Auth::login($findUser);
			return redirect('/courier');
		} else {
			$user = new User;
			$user->name = $userSocialite->name;
			$user->email = $userSocialite->email;
			// $user->profile = $userSocialite->avatar;
			// return $user;
			// $user->status = '0';
			$user->password = Hash::make('password');
			$user->save();
			Auth::login($userSocialite->email);
			return redirect('/');
		}
	}


	/**
	 * Get the needed authorization credentials from the request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	// protected function credentials(Request $request)
	// {
	//     $credentials = $request->only($this->username(), 'password');
	//     $credentials['status'] = 1;
	//     return $credentials;
    // }


 /**
     * Send the post-authentication response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return \Illuminate\Http\Response
     */
    private function authenticated(Request $request, Authenticatable $user)
    {
        if ($user->google2fa_secret) {
            Auth::logout();

            $request->session()->put('2fa:user:id', $user->id);

            return redirect('2fa/validate');
        }

        return redirect()->intended($this->redirectTo);
    }
     /**
     *
     * @return \Illuminate\Http\Response
     */
    public function getValidateToken()
    {

        if (session('2fa:user:id')) {
            return view('2fa/validate');
        }

        return redirect('login');
    }
     /**
     *
     * @param  App\Http\Requests\ValidateSecretRequest $request
     * @return \Illuminate\Http\Response
     */
    public function postValidateToken(ValidateSecretRequest $request)
    {

        //get user id and create cache key
        $userId = $request->session()->pull('2fa:user:id');
        $key    = $userId . ':' . $request->otp;

        //use cache to store token to blacklist
        Cache::add($key, true, 4);

        //login and redirect user
        Auth::loginUsingId($userId);

        return redirect()->intended($this->redirectTo);
    }
}
