<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AjoutUtilisateurType;
use App\Entity\Utilisateur;

class UtilisateurController extends AbstractController
{
    /**
     * @Route("/ajout_utilisateur", name="ajout_utilisateur")
     */
    public function ajout_utilisateur(Request $request)
    {
        $user = new Utilisateur();
        $user-> setDateinscription(new \DateTime());

        $form = $this->createForm(AjoutUtilisateurType::class, $user); 
        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->addFlash('notice','Utilisateur inséré');
                return $this->redirectToRoute('ajout_utilisateur');        
            }          
        }        
        return $this->render('utilisateur/ajout_utilisateur.html.twig', [            
            'form'=>$form->createView()        
        ]);
    }

    /**
     * @Route("/liste_utilisateurs", name="liste_utilisateurs")
     */
    public function liste_utilisateurs(Request $request)
    {
        $em = $this->getDoctrine();
        $repoTheme = $em->getRepository(Utilisateur::class);
        $users = $repoTheme->findBy(array(), array('dateinscription'=>'ASC'));
        return $this->render('utilisateur/liste_utilisateurs.html.twig', [            
            'users'=>$users
        ]);
    }
}
