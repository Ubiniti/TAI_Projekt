<?php

namespace App\Controller;

use App\Controller\AuthenticationController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\User;
use App\Entity\LoggedInUsers;

class LoginController extends AuthenticationController
{
    /**
     * @Route("/logform", name="logform")
     */
    public function index()
    {
        $user = $this->getLoggedInUser();
        if($user)
        {
            return $this->redirectToRoute('shop');
        }
        
        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $user = new User();

        $username = $request->request->get("username");
        $plainPassword = $request->request->get("password");

        $user = $userRepository->findOneByUsername($username);

        if(!$user)
        {
            $this->get('session')->getFlashBag()->add('error', 'No such username!');
            return $this->redirectToRoute('logform');
        }

        $validCredentials = $encoder->isPasswordValid($user, $plainPassword);

        if(!$validCredentials)
        {
            $this->get('session')->getFlashBag()->add('error', 'Invalid password!');
            return $this->redirectToRoute('logform');
        }

        $this->updateUserInSession($username);

        return $this->redirectToRoute('account_management');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $loggedInUsersRepository = $this->getDoctrine()->getRepository(LoggedInUsers::class);

        $session = new Session();
        $sessionid = $session->getId();

        $loggedInUsersRow = $loggedInUsersRepository->findOneBySessionId($sessionid);

        if(!$loggedInUsersRow)
        {
            $this->get('session')->getFlashBag()->add('error', 'You are not logged in!');
            return $this->redirectToRoute('logform');
        }

        $entityManager->remove($loggedInUsersRow);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add('success', 'You have been logged out successfully!');

        return $this->redirectToRoute('logform');
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        
        $user = new User();
        $username = $request->request->get("username");
        $email = $request->request->get("email");
        $plainPassword = $request->request->get("password");
        $encoded = $encoder->encodePassword($user, $plainPassword);

        $user->setUsername( $username )
            ->setEmail( $email )
            ->setPassword( $encoded );

        if($userRepository->findOneByUsername($username))
        {
            $this->get('session')->getFlashBag()->add('error', 'Account with this USERNAME already EXISTS!');
            return $this->redirectToRoute('logform');
        }

        if($userRepository->findOneByEmail($email))
        {
            $this->get('session')->getFlashBag()->add('error', 'Account with this EMAIL address already EXISTS!');
            return $this->redirectToRoute('logform');
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add('success', 'Account created successfully!');

        return $this->forward('App\Controller\LoginController::login', [
            'username' => $username,
            'password' => $plainPassword
        ]);
    }
}
