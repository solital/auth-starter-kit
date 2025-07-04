<?php

namespace Solital\Components\Controller\Auth;

use Solital\Core\Auth\Auth;
use Solital\Core\Course\Http\Controller;
use Solital\Core\Security\Guardian;
use Solital\AuthKit\AuthModel;

class LoginController extends Controller
{
    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function auth()
    {
        Auth::isRemembering();

        return view('auth.auth-form', [
            'title' => 'Login',
            'msg' => (message()->get('login') ?? message()->get(Guardian::GUARDIAN_MESSAGE_INDEX))
        ]);
    }

    /**
     * @return void
     */
    public function authPost(): void
    {
        if ($this->requestLimit('email.login', 3)) {
            message()->new('login', 'You have already made 3 login attempts! Please wait 60 seconds and try again.');
            response()->redirect(url('auth'));
        }

        $email = input()->post('email')->getValue();
        $password = input()->post('password')->getValue();

        $result = Auth::login(AuthModel::class, [
            "username" => $email,
            "password" => $password
        ]);

        if ($result == false) {
            message()->new('login', 'Invalid username and/or password!');
            response()->redirect(url('auth'));
        }
    }

    /**
     * @return mixed
     */
    public function dashboard()
    {
        Guardian::allowFromTable('auth_users');

        return view('auth.auth-dashboard', [
            'title' => 'Dashboard',
        ]);
    }

    /**
     * @return void
     */
    public function exit(): void
    {
        message()->new('login', 'Logoff successfully!');
        Auth::logoff();
    }
}
