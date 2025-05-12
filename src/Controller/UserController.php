<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository ): Response
    {
        return $this->render('user/index.html.twig', [
            'users'=> $userRepository->findAll(),
        ]);
    }
    #[Route('/admin/user/{id}/to/editor', name: 'app_user_to_editor')]
    public function ChangeRole(User $user , EntityManagerInterface $entityManager): Response
    {
        $user->setRoles(["ROLE_EDITOR","ROLE_USER"]);
        $entityManager->flush();
        $this->addFlash("success","Le role éditeur a été ajouté a votre utilisateur");
        return $this->redirectToRoute('app_user');
    }
    #[Route('/admin/user/{id}/remove/editor/role', name:'app_user_remove_editor_role')]
    public function editRoleRemove (User $user , EntityManagerInterface $entityManager): Response
    {
        $user->setRoles(['']);
        $entityManager->flush();
        $this->addFlash('danger','Le role éditeur a été supprimé avec succés');
        return $this->redirectToRoute('app_user');
    }
    #[Route('/admin/user/{id}/remove/', name:'app_user_remove')]
    public function userRemove(EntityManagerInterface $entityManager ,$id , UserRepository $userRepository): Response
    {
        $userFind=$userRepository->find($id);
        $entityManager->remove($userFind);
        $entityManager->flush();

        $this->addFlash('danger','Votre utilisateur a été supprimé');
        return $this->redirectToRoute('app_user');
    }

    #[Route('/user/{id}/orders', name: 'user_orders')]
    public function userOrders(int $id, UserRepository $userRepository): Response
    {
        // Récupérer l'utilisateur par son ID
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Récupérer les commandes de l'utilisateur
        $userOrders = $user->getUserOrders();

        // Passer les commandes à la vue
        return $this->render('user/orders.html.twig', [
            'user' => $user,
            'userOrders' => $userOrders,
        ]);
    }
}
