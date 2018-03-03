<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AuthManager;
use App\Service\TokenManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class GoogleController extends Controller
{

    private $authManager;
    private $tokenManager;

    public function __construct(AuthManager $authManager,
                                TokenManager $tokenManager)
    {
        $this->authManager = $authManager;
        $this->tokenManager = $tokenManager;
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
        $token = new UsernamePasswordToken($user, null, 'main', $role);
        $this->container->get('security.token_storage')->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);

        $this->container->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        $token = $this->getDoctrine()
            ->getRepository('App:UserToken')
            ->findOneBy(['user' => $user]);
        if (!is_null($token)) {
            $token = $this->tokenManager
                ->generateToken($user);
            $this->tokenManager
                ->save($token);
        }
//        $request->getSession()->set("token", $token->getToken());


        return $this->redirect('http://localhost:8080/#/login/' . $token);
    }

}
