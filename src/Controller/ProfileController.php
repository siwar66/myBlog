<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserProfileType;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si un utilisateur est connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Créer un formulaire pour modifier les informations de l'utilisateur
        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        // Gérer le formulaire soumis
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du téléchargement de fichier pour 'profilePicture'
            /** @var UploadedFile $file */
            $file = $form->get('profilePicture')->getData();
            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();
                $file->move(
                    $this->getParameter('profile_pictures_directory'), // Configurez ce paramètre
                    $fileName
                );

                // Mettre à jour la photo de profil
                $user->setProfilePicture($fileName);
            }

            // Sauvegarder les modifications
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
