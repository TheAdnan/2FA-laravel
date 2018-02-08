<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Twilio\Rest\Client;


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
    protected $redirectTo = '/user/verify';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    public function authenticate(Request $request, $code){
        $user = User::where('code', $code)->first();
        $enter_token = false;
        if($user){
            $user->code = '';
            $user->authenticated = 1;
            if($user->save()){
                if($this->sendSMS($user->phone, $user->sms_code)){
                    $success = "Success! Check your SMS to finish the last step of the registration";
                    $enter_token = true;
                    return view('success', compact('success', 'enter_token'));
                }
                else{
                    $error = "Could not send SMS";
                    return view('notification', compact('error', 'enter_token'));
                }
            }
            else{
                $error = "An error occurred";
                return view('notification', compact('error', 'enter_token'));
            }
        }
        else{
            $error = "You have already finished the first step of this 2FA registration. Check your SMS to finish the registration process";
            $enter_token = true;
            return view('notification', compact('error', 'enter_token'));
        }
    }

    public function authenticateToken(Request $request)
    {
        if(null !== $request->input('sms_token')){
            $code = $request->input('sms_token');
            $user = User::where('sms_token', $code)->first();
            if($user){
                $user->sms_token = '';
                $user->authenticated = 2;
                if($user->save()){
                    $success = "You have been successfully authenticated!";
                    return view('success', compact('success'));
                }
                else{
                    $error = "An error occurred";
                    return view('notification', compact('error'));
                }
            }
            else{
                $error = "You are already authenticated";
                return view('notification', compact('error'));
            }
        }
    }

    private function sendSMS($number, $code){
        $sid = env("TWILIO_SID");
        $token = env("TWILIO_TOKEN");
        $client = new Client($sid, $token);
        $message = $client->messages->create(
            $number,
            array(
                'from' => env("TWILIO_NUMBER"),
                'body' => "Your authentication code is: $code"
            )
        );
    }

}
