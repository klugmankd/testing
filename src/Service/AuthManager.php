<?php

namespace App\Service;

use App\Entity\User;
use Overtrue\Socialite\SocialiteManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthManager
{
    private $config;
    private $driver;

    public function __construct()
    {
        $this->config = [
            'google' => [
                'client_id' => "833066078493-trlmj3g7k16vjiigseq492shum1og2pb.apps.googleusercontent.com",
                'client_secret' => "3WbViUVA02ITuiiHF8mqj0T2",
                'redirect' => "http://127.0.0.1:8000/connect/google/check"
            ]
        ];
        $socialite = new SocialiteManager($this->config);
        $this->driver = $socialite->driver('google');
    }

    /**
     * @return \Overtrue\Socialite\ProviderInterface
     */
    public function getDriver(): \Overtrue\Socialite\ProviderInterface
    {
        return $this->driver;
    }


}