<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserManagementController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_user_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les utilisateurs
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/user_list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/validate/{id}', name: 'admin_user_validate')]
    public function validate(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Exemple : Ajouter un rôle pour valider le compte
        $roles = $user->getRoles();
        if (!in_array('ROLE_VALIDATED', $roles)) {
            $roles[] = 'ROLE_VALIDATED';
            $user->setRoles($roles);
            $entityManager->flush();
        }

        $this->addFlash('success', 'Le compte utilisateur a été validé avec succès.');

        return $this->redirectToRoute('admin_user_list');
    }

    #[Route('/admin/users/deactivate/{id}', name: 'admin_user_deactivate')]
    public function deactivate(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Exemple : Désactiver un compte utilisateur
        $user->setRoles([]); // Supprime tous les rôles
        $entityManager->flush();

        $this->addFlash('success', 'Le compte utilisateur a été désactivé.');

        return $this->redirectToRoute('admin_user_list');
    }
}
