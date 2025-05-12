<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServicesController extends AbstractController
{
    #[Route('/services', name: 'app_services')]
    public function index(): Response
    {
        $engagements = [
            [
                'icon' => 'fas fa-lock-open',
                'color' => 'primary',
                'title' => 'Accès Libre',
                'description' => 'Aucune barrière financière à la connaissance',
                'badge' => 'Nouveau',
                'badge_color' => 'primary'
            ],
            [
                'icon' => 'fas fa-recycle',
                'color' => 'success',
                'title' => 'Économie Circulaire',
                'description' => 'Optimisation des ressources littéraires',
                'badge' => 'Éco-friendly',
                'badge_color' => 'success'
            ],
            [
                'icon' => 'fas fa-hands-helping',
                'color' => 'info',
                'title' => 'Entraide Communautaire',
                'description' => 'Système basé sur la confiance mutuelle',
                'badge' => 'Collaboratif',
                'badge_color' => 'info'
            ]
        ];

        $fonctionnements = [
            [
                'icon' => 'fas fa-user-plus',
                'color' => 'primary',
                'title' => 'Adhésion Simplifiée',
                'description' => 'Créez votre profil en 2 minutes',
                'badge' => 'Nouveau',
                'badge_color' => 'primary'
            ],
            [
                'icon' => 'fas fa-book-open',
                'color' => 'success',
                'title' => 'Gestion des Livres',
                'description' => 'Système de prêt intelligent',
                'badge' => 'Populaire',
                'badge_color' => 'success'
            ],
            [
                'icon' => 'fas fa-comments',
                'color' => 'info',
                'title' => 'Interactions Sociales',
                'description' => 'Partagez vos impressions',
                'badge' => 'Communautaire',
                'badge_color' => 'info'
            ]
        ];

        return $this->render('services/index.html.twig', [
            'engagements' => $engagements,
            'fonctionnements' => $fonctionnements
        ]);
    }
}