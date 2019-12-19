<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

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
     * Create a new controller instance.
     *
     * @return void
     */
    public function loginDistant(Request $request)
    {
        $password = $request->password;
        $email = $request->email;
        //$apiman = "Bearer {$this->accesstokenApi()}";
        $client = new Client();
        $response = $client->post('http://127.0.0.1:8888/api/login', [
            'headers' => 
            [
                //'authorization' => $apiman, 
                'content-type'  => 'application/json'
            ],
            'json' => 
            [
                'email' => $email,
                'password' => $password
            ],
        ]);
        $data = json_decode((string) $response->getBody(), true);
        if ($data['token_type']=="bearer") {
            session()->put('token', $data);
            return redirect('/');
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }
        //return redirect()->away('http://127.0.0.1:8000/api/login?email=test@test.fr&password=pass');
    }
}
