<?php

namespace App\Controller;

use App\Controller\AuthenticationController;

use App\Entity\User;
use App\Entity\Product;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ShopController extends AuthenticationController
{
    /**
     * @Route("/shop/category/{category}", name="shop")
     */
    public function index($category = "meble")
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $this->getDoctrine()->getRepository(Product::class);

        $user = $this->getLoggedInUser();
        $user_data = array();

        if($user)
        {
            $user_data = $user->toArray();
        }

        $products = $productRepository->findByCategory($category);

        $data = array();
        for($i = 0; $i < count($products); $i++)
        {
            $data[$i] = $products[$i]->toArray();
        }
        
        return $this->render('shop/index.html.twig', [
            'controller_name' => 'ShopController',
            'user' => $user,
            'category' => $category,
            'products' => $data
        ]);
    }

    /**
     * @Route("/shop/preview/{id}", name="shop_preview", requirements={"page"="\d+"})
     */
    public function preview($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $this->getDoctrine()->getRepository(Product::class);

        $user = $this->getLoggedInUser();
        $user_data = array();

        if($user)
        {
            $user_data = $user->toArray();
        }

        $product = $productRepository->findOneById($id);

        $category = $product->getCategory();

        $data = $product->toArray();

        return $this->render('shop/preview.html.twig', [
            'controller_name' => 'ShopController',
            'user' => $user,
            'category' => $category,
            'product' => $data
        ]);
    }
    
    /**
     * @Route("/shop/add", name="add_product")
     */
    public function addProduct(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $veryfication = $this->verifyLogin();
        if($veryfication)
        {
            return $veryfication;
        }

        $user = $this->getLoggedInUser();

        $product = new Product();

        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class, ['label' => 'Nazwa'])
            ->add('description', TextareaType::class, ['label' => 'Opis'])
            ->add('price', MoneyType::class, ['label' => 'Cena'])
            ->add('image', FileType::class, ['label' => 'Zdjęcie'])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Elektronika' => 'elektronika',
                    'Meble' => 'meble',
                    'Ubrania' => 'ubrania'
                ],
                'label' => 'Kategoria'
            ])
            ->add('save', SubmitType::class, ['label' => 'Dodaj produkt'])
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $product = $form->getData();

            $file = $form->get('image')->getData();

            $uniqueName = md5(uniqid());
            $filename = $uniqueName.'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $filename
                );
            } catch (FileException $e) {
                return new Response("Error while uploading image!");
            }

            $product->setImage($filename);
            $product->setAdded(new \DateTime('now'));
            $product->setOwner($user->getUsername());

            $entityManager->persist($product);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', 'Product added successfuly!');

            return $this->render('shop/confirm.html.twig', [
                'controller_name' => 'ShopController',
                'user' => $user->toArray(),
                'product' => $product->toArray()
            ]);
        }

        return $this->render('shop/add.html.twig', [
            'controller_name' => 'ShopController',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    public function getProduct($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $this->getDoctrine()->getRepository(Product::class);

        $product = $productRepository->findOneById($id);

        if(!$product)
        {
            $this->get('session')->getFlashBag()->add('error', 'Product does not exist!');
        }

        return $product;
    }

    public function verifyPrivileges($product, $user)
    {
        $username = $user->getUsername();
        $productOwner = $product->getOwner();
        if($username != $productOwner)
        {
            $this->get('session')->getFlashBag()->add('error', 'This product is not yours!');
            return false;
        }
        return true;
    }

    /**
     * @Route("/shop/remove/{id}", name="shop_remove", requirements={"page"="\d+"})
     */
    public function remove($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $veryfication = $this->verifyLogin();
        if($veryfication)
        {
            return $veryfication;
        }

        $user = $this->getLoggedInUser();

        $product = $this->getProduct($id);

        $priviledges = $this->verifyPrivileges($product, $user);
        if(!$priviledges || !$product)
        {
            return $this->redirectToRoute('account_myproducts');
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $this->get('session')->getFlashBag()->add('success', 'Product removed from database successfuly!');

        return $this->redirectToRoute('account_myproducts');
    }
}
