<?php

namespace App\Controller;

use App\Entity\LoggedInUsers;
use App\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Controller\AuthenticationController;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
     * @Route("/account/myproducts", name="account_myproducts")
     */
    public function myproducts()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $this->getDoctrine()->getRepository(Product::class);

        $veryfication = $this->verifyLogin();
        if($veryfication)
        {
            return $veryfication;
        }
        
        $user = $this->getLoggedInUser();

        $owner = $user->getUsername();
        $products = $productRepository->findByOwner($owner);

        return $this->render('account/my_products.html.twig', [
            'controller_name' => 'AccountController',
            'user' => $user->toArray(),
            'products' => $products
        ]);
    }

    /**
     * @Route("/account/edit", name="account_edit")
     */
    public function edit(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $veryfication = $this->verifyLogin();
        if($veryfication)
        {
            return $veryfication;
        }
        
        $user = $this->getLoggedInUser();

        $form = $this->createFormBuilder($user)
            ->add('username', TextType::class, ['label' => 'Nazwa użytkownika'])
            ->add('email', TextType::class, ['label' => 'Adres email'])
            ->add('save', SubmitType::class, ['label' => 'Edytuj'])
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $user = $form->getData();

            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', 'User edited successfuly!');

            return $this->redirectToRoute('account_edit');
        }

        return $this->render('account/edit.html.twig', [
            'controller_name' => 'AccountController',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

}
