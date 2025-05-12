<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

final class EmpruntController extends AbstractController
{
    #[Route('/emprunt', name: 'app_emprunt')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $emprunts = $entityManager->getRepository(Emprunt::class)->findAll();
        
        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $emprunts,
        ]);
    }

    // src/Controller/EmpruntController.php

#[Route('/emprunt/new/{productId}', name: 'app_emprunt_new')]
public function new(int $productId, EntityManagerInterface $entityManager): Response
{
    $product = $entityManager->getRepository(Product::class)->find($productId);
    


    // Vérifier si le produit est disponible
    if ($product->getStock() <= 0) {
        $this->addFlash('error', 'Ce produit n\'est plus disponible pour le moment.');
        return $this->redirectToRoute('app_home_product_show', ['id' => $productId]);
    }

    $user = $this->getUser();
    
    // Vérification plus robuste des emprunts existants
    $existingEmprunt = $entityManager->getRepository(Emprunt::class)->createQueryBuilder('e')
        ->where('e.user = :user')
        ->andWhere('e.product = :product')
        ->andWhere('e.statut IN (:statuts)')
        ->setParameter('user', $user)
        ->setParameter('product', $product)
        ->setParameter('statuts', ['emprunté', 'reserved']) // Tous les statuts non terminés
        ->getQuery()
        ->getOneOrNullResult();

    if ($existingEmprunt) {
        $this->addFlash('error', 'Vous avez déjà emprunté ce produit et ne l\'avez pas encore retourné.');
        return $this->redirectToRoute('app_home_product_show', ['id' => $productId]);
    }

    $emprunt = new Emprunt();
    $emprunt->setUser($user);
    $emprunt->setProduct($product);
    $emprunt->setDateEmprunt(new \DateTime());
    $emprunt->setDateRetour((new \DateTime())->modify('+7 days'));

    // Décrémenter le stock
    $product->decrementStock();

    $entityManager->persist($emprunt);
    $entityManager->flush();

    $this->addFlash('success', 'Emprunt effectué avec succès!');

    return $this->redirectToRoute('app_emprunt');
}

#[Route('/emprunt/return/{id}', name: 'app_emprunt_return')]
public function returnEmprunt(int $id, EntityManagerInterface $entityManager): Response
{
    $emprunt = $entityManager->getRepository(Emprunt::class)->find($id);
    
    if (!$emprunt) {
        throw $this->createNotFoundException('Emprunt non trouvé');
    }

    // Incrémenter le stock du produit
    $product = $emprunt->getProduct();
    $product->incrementStock();

    $entityManager->remove($emprunt);
    $entityManager->flush();

    $this->addFlash('success', 'Retour effectué avec succès! Le produit est à nouveau disponible.');

    return $this->redirectToRoute('app_emprunt');
}

// Dans EmpruntController.php


#[Route('/emprunt/delete/{id}', name: 'app_emprunt_delete', methods: ['POST','GET'])]
public function deleteReservation(int $id, EntityManagerInterface $entityManager): Response
{
    $emprunt = $entityManager->getRepository(Emprunt::class)->find($id);

    if (!$emprunt) {
        throw $this->createNotFoundException('Emprunt non trouvé');
    }


    // Incrémenter le stock si nécessaire
    $emprunt->getProduct()->incrementStock();

    $entityManager->remove($emprunt);
    $entityManager->flush();

    $this->addFlash('danger', 'La réservation a été supprimée avec succès.');

    return $this->redirectToRoute('app_emprunt');
}

// Dans EmpruntController.php

#[Route('/emprunt/reservation/{id}', name: 'app_emprunt_reservation')]
public function reservation(int $id, EntityManagerInterface $entityManager): Response
{
    $product = $entityManager->getRepository(Product::class)->find($id);
    

    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Vérifier si l'utilisateur a déjà emprunté ce produit et ne l'a pas encore retourné
    $existingEmprunt = $entityManager->getRepository(Emprunt::class)->createQueryBuilder('e')
        ->where('e.user = :user')
        ->andWhere('e.product = :product')
        ->andWhere('e.statut IN (:statuts)')
        ->setParameter('user', $user)
        ->setParameter('product', $product)
        ->setParameter('statuts', ['emprunté', 'reserved'])
        ->getQuery()
        ->getOneOrNullResult();

    if ($existingEmprunt) {
        $this->addFlash('error', 'Vous avez déjà une réservation ou un emprunt en cours pour ce produit.');
        return $this->redirectToRoute('app_home_product_show', ['id' => $id]);
    }

    // Vérification stricte du stock
    if ($product->getStock() <= 0) {
        $nextReturnDate = $entityManager->getRepository(Emprunt::class)
            ->findNextReturnDateForProduct($product);
            
        $this->addFlash('error', 'Ce produit n\'est plus disponible. Il sera à nouveau disponible le '.$nextReturnDate->format('d/m/Y'));
        return $this->redirectToRoute('app_home_product_show', ['id' => $id]);
    }

    return $this->render('emprunt/reservation.html.twig', [
        'product' => $product,
    ]);
}

#[Route('/mes-reservations', name: 'app_mes_reservations')]
public function mesReservations(EntityManagerInterface $entityManager): Response
{
    $user =$this->getUser();
    
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    $reservations = $entityManager->getRepository(Emprunt::class)->findBy([
        'user' => $user,
        'statut' => 'reserved' // ou autre statut selon votre besoin
    ], ['date_emprunt' => 'DESC']);

    return $this->render('emprunt/mes_reservations.html.twig', [
        'reservations' => $reservations,
    ]);
}

#[Route('/emprunt/confirm/{id}', name: 'app_emprunt_confirm', methods: ['POST'])]
public function confirmReservation(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $product = $entityManager->getRepository(Product::class)->find($id);
    
    if (!$product) {
        throw $this->createNotFoundException('Produit non trouvé');
    }

    // Vérifier à nouveau le stock avant confirmation
    if ($product->getStock() <= 0) {
        $nextReturnDate = $entityManager->getRepository(Emprunt::class)
            ->findNextReturnDateForProduct($product);
            
        $this->addFlash('error', 'Ce produit n\'est plus disponible. Il sera à nouveau disponible le '.$nextReturnDate->format('d/m/Y'));
        return $this->redirectToRoute('app_home_product_show', ['id' => $id]);
    }

    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    // Créer un nouvel emprunt
    $emprunt = new Emprunt();
    $emprunt->setUser($user);
    $emprunt->setProduct($product);
    $emprunt->setDateEmprunt(new \DateTime());
    $emprunt->setDateRetour((new \DateTime())->modify('+7 days'));
    $emprunt->setStatut('reserved');

    // Décrémenter le stock
    $product->decrementStock();

    $entityManager->persist($emprunt);
    $entityManager->flush();

    $this->addFlash('success', 'Votre réservation a été confirmée avec succès!');

    return $this->redirectToRoute('app_mes_reservations');
}



}