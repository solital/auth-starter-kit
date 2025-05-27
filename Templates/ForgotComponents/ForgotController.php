<?php

namespace Solital\Components\Controller\Auth;

use Solital\Core\Auth\Password;
use Solital\Core\Course\Http\Controller;
use Solital\Core\Security\Hash;
use Solital\Core\Wolf\Wolf;
use Solital\AuthKit\AuthModel;

class ForgotController extends Controller
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
    public function forgot()
    {
        return view('auth.forgot-form', [
            'title' => 'Forgot Password',
            'msg' => message()->get('forgot')
        ]);
    }

    /**
     * @return void
     */
    public function forgotPost(): void
    {
        $email = $this->getRequestParams()->post('email')->getValue();

        if ($this->requestRepeat('email.forgot', $email)) {
            message()->new('forgot', 'You have tried this email before!');
            response()->redirect(url('forgot'));
        }

        $generated_link = Password::generateLink(
            ['email' => $email],
            url('change'),
            '+2 hours'
        );

        $wolf = new Wolf;
        $wolf->setArgs(['link' => $generated_link]);
        $wolf->setView('auth.template-recovery-password');
        $template = $wolf->render();

        $link_send = Password::sendGeneratedLinkTo(
            'solital@email.com',
            'User',
            $template
        );

        ($link_send == true)
            ? message()->new('forgot', 'Link sent to your email!')
            : message()->new('forgot', 'E-mail not exists!');

        response()->redirect(url('forgot'));
    }

    /**
     * @param string $hash
     *
     * @return mixed
     */
    public function change($hash)
    {
        $res = Hash::decrypt($hash)->isValid();

        if ($res == true) {
            $email = Hash::decrypt($hash)->value();

            return view('auth.forgot-change-pass', [
                'title' => 'Change Password',
                'email' => $email,
                'hash' => $hash,
                'msg' => message()->get('forgot')
            ]);
        }

        message('login', 'The informed link has already expired!');
        response()->redirect(url('auth'));
    }

    /**
     * @param string $hash
     *
     * @return void
     */
    public function changePost($hash): void
    {
        $result = Hash::decrypt($hash)->isValid();
        if ($result == false)
            response()->redirect(url('auth'));

        $pass = $this->getRequestParams()->post('inputPass')->getValue();
        $confPass = $this->getRequestParams()->post('inputConfPass')->getValue();

        if ($pass != $confPass) {
            message()->new('forgot', 'The fields do not match!');
            response()->redirect(url('change', ['hash' => $hash]));
        }

        $changed = Password::reset(
            AuthModel::class,
            ['username' => $hash],
            ['password' => $pass]
        );

        ($changed == true)
            ? message()->new('login', 'Password changed successfully!')
            : message()->new('login', 'Error to change the Password');

        response()->redirect(url('auth'));
    }
}
