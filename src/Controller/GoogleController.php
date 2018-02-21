<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\GoogleAuthenticator;
use App\Service\AuthManager;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class GoogleController extends Controller
{

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/connect/google", name="connect_google")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connectAction()
    {
        return $this->authManager
            ->getDriver()
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
        $googleUser = $this->authManager
            ->getDriver()
            ->user();

        $user = $this->getDoctrine()
            ->getRepository('App:User')
            ->findOneBy(['googleId' => $googleUser->getId()]);


        if (is_null($user)) {
            $user = new User();
            $user->setEmail(strval($googleUser->getEmail()));
            $user->setGoogleId(strval($googleUser->getId()));
            $user->setAdmin(false);
            $entityManager = $this->getDoctrine()
                ->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        }

        $request->getSession()->set("user", $user);
        $role = ($user->isAdmin()) ? ['ROLE_ADMIN'] : ['ROLE_USER'];
        $token = new UsernamePasswordToken($user->getUsername(), null, 'main', $role);
        $this->container->get('security.token_storage')->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);

        $this->container->get('event_dispatcher')->dispatch('security.interactive_login', $event);
        return $this->redirectToRoute('app_home');
    }

}
