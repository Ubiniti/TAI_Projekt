<?php

namespace App\Controller;

use App\Entity\LoggedInUsers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Controller\AuthenticationController;

class AccountController extends AuthenticationController
{
    /**
     * @Route("/account/", name="account_management")
     */
    public function index()
    {
        $veryfication = $this->verifyLogin();
        if($veryfication)
        {
            return $veryfication;
        }
        
        $user = $this->getLoggedInUser();
        
        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'user' => $user->toArray()
        ]);
    }

    /**
     * @Route("/account/show", name="account_show")
     */
    public function show()
    {
        $veryfication = $this->verifyLogin();
        if($veryfication)
        {
            return $veryfication;
        }
        
        $user = $this->getLoggedInUser();

        return $this->render('account/show.html.twig', [
            'controller_name' => 'AccountController',
            'user' => $user->toArray()
        ]);
    }

    /**
     * @Route("/account/edit", name="account_edit")
     */
    public function edit()
    {
        $veryfication = $this->verifyLogin();
        if($veryfication)
        {
            return $veryfication;
        }
        
        $user = $this->getLoggedInUser();

        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController'
        ]);
    }

}
