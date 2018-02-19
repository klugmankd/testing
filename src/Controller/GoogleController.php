<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\GoogleAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GoogleController extends Controller
{

    private $googleAuthenticator;

    public function __construct(GoogleAuthenticator $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/google", name="connect_google")
     * @param ClientRegistry $clientRegistry
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect();
    }

    /**
     * Facebook redirects to back here afterwards
     *
     * @Route("/connect/google/check", name="connect_google_check")
     * @param Request $request
     * @return Response
     */
    public function connectCheckAction(Request $request)
    {

        $client = $this->get('oauth2.registry')
            ->getClient('google');

        $user = $client->fetchUser();

        return new Response($user->getId());
    }

}
