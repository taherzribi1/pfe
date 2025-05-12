<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\AddProductHistoryType; 
use App\Form\ProductType;
use App\Form\ProductUpdateType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as AnnotationRoute;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        #[Autowire("%image_dir%")] string $image_dir
    ): Response {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            if ($photo = $form['image']->getData()) {
                $fileName = uniqid() . "." . $photo->guessExtension();
                $photo->move($image_dir, $fileName);
                $product->setImage($fileName);
            }
            
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Votre livre a été ajouté');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    // CORRECTION APPLIQUÉE ICI
    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Product $product, 
        EntityManagerInterface $entityManager,
        #[Autowire("%image_dir%")] string $image_dir // Ajouter l'injection du répertoire d'images
    ): Response {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de la nouvelle image
            if ($photo = $form['image']->getData()) {
                $fileName = uniqid() . "." . $photo->guessExtension();
                $photo->move($image_dir, $fileName);
                
                // Supprimer l'ancienne image si elle existe
                $oldImage = $product->getImage();
                if ($oldImage) {
                    $oldImagePath = $image_dir . '/' . $oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                $product->setImage($fileName);
            }
    
            $entityManager->flush();
            $this->addFlash('info', 'Votre livre a été modifié');
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
public function delete(
    Request $request, 
    Product $product, 
    EntityManagerInterface $entityManager
): Response {
    if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('danger', 'Votre livre a été supprimé');
    }

    return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
}


    #[Route('/nouveautes', name: 'app_product_latest', methods: ['GET'])]
    public function latest(ProductRepository $productRepository): Response
    {
        $lastProducts = $productRepository->findBy([], ['id' => 'DESC'], 8);
        return $this->render('nouveaute/index.html.twig', [
            'products' => $lastProducts,
        ]);
    }
}