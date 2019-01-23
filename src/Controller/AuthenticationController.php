<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Entity\LoggedInUsers;
use App\Entity\User;

class AuthenticationController extends AbstractController
{
    public function verifyLogin()
    {
         $user = $this->getLoggedInUser();
         if(!$user)
         {
             $this->get('session')->getFlashBag()->add('error', 'You are not logged in!');
             return $this->redirectToRoute('logform');
         }
         return null;
    }
    
    public function getLoggedInUser()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $loggedInUsersRepository = $this->getDoctrine()->getRepository(LoggedInUsers::class);
        
        $session = new Session();
        $sessionid = $session->getId();
        $loggedInUsersRow = $loggedInUsersRepository->findOneBySessionid($sessionid);
        if(!$loggedInUsersRow)
        {
            return null;
        }

        $lastSessionUpdate = $loggedInUsersRow->getLastupdate();
        $now = new \DateTime('now');
        if($now->sub(new \DateInterval('PT15M')) > $lastSessionUpdate)
        {
            $this->get('session')->getFlashBag()->add('error', 'Session expired!');
            $entityManager->remove($loggedInUsersRow);
            $entityManager->flush();

            return null;
        }
        else
        {
            $loggedInUsersRow->setLastupdate(new \DateTime("now"));
            $entityManager->flush();
        }
        
        $username = $loggedInUsersRow->getUsername();

        $user = $userRepository->findOneByUsername($username);

        return $user;
    }

    public function updateUserInSession($username)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $loggedInUsersRepository = $this->getDoctrine()->getRepository(LoggedInUsers::class);

        $session = new Session();
        $sessionid = $session->getId();

        $row = $loggedInUsersRepository->findOneByUsername($username);
        if(!$row)
        {
            $row = new LoggedInUsers();
            $row->setSessionid($sessionid);
            $row->setUsername($username);
            $row->setLastupdate(new \DateTime("now"));
            $entityManager->persist($row);
        }
        else
        {
            $row->setLastupdate(new \DateTime("now"));
        }
        
        $entityManager->flush();
    }
}
