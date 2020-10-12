<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AjoutUtilisateurType;
use App\Form\ModifUtilisateurType;
use App\Entity\Utilisateur;
use App\Form\ProfilUtilisateurType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        $repoUser = $em->getRepository(Utilisateur::class);

        if($request->get('supp')!=null){
            $user = $repoUser->find($request->get('supp'));
            if($user!=null){
                $em->getManager()->remove($user);
                $em->getManager()->flush();
            }
            $this->redirectToRoute('liste_utilisateurs');
        }

        if($request->get('connect')!=null){
            $user = $repoUser->find($request->get('connect'));
            if($user!=null){
                $session = new Session();
                $session->start();
                $session->set('user', $user);
            }
            $this->redirectToRoute('liste_utilisateurs');
        }

        $users = $repoUser->findBy(array(), array('dateinscription'=>'ASC'));
        return $this->render('utilisateur/liste_utilisateurs.html.twig', [            
            'users'=>$users
        ]);
    }

    /**
     * @Route("/modif_utilisateur/{id}", name="modif_utilisateur", requirements={"id"="\d+"})
     */
    public function modif_utilisateur(int $id, Request $request)
    {
        $em = $this->getDoctrine();
        $repoUser = $em->getRepository(Utilisateur::class);
        $user = $repoUser->find($id);

        if($user==null){
            $this->addFlash('notice','Cette page n\'existe pas');
            return $this->redirectToRoute('liste_utilisateurs');   
        }

        $form = $this->createForm(ModifUtilisateurType::class,$user);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->addFlash('notice','Utilisateur modifié');
                return $this->redirectToRoute('liste_utilisateurs');        
            }          
        } 

        return $this->render('utilisateur/modif_utilisateur.html.twig', [            
            'form'=>$form->createView()        
        ]);
    }

    /**
     * @Route("/user_profile/{id}", name="user_profile", requirements={"id"="\d+"})
     */
    public function user_profile(int $id, Request $request)
    {
        $em = $this->getDoctrine();
        $repoUser = $em->getRepository(Utilisateur::class);
        $user = $repoUser->find($id);
        $form = $this->createFormBuilder($user)
        ->add('photo', FileType::class, array('label' => 'Fichier à télécharger'))
        ->add('send', SubmitType::class, array('label' => 'Modifier'))->getForm();

        if($user==null){
            $this->addFlash('notice','Cette page n\'existe pas');
            return $this->redirectToRoute('liste_utilisateurs');   
        }

        $form = $this->createForm(ProfilUtilisateurType::class, $user); 
        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $file = $user->getPhoto();
                $fileName = $user->getPrenom().$user->getNom().'.'.$file->guessExtension();
                $em->persist($user);
                $em->flush();

                try{
                    $file->move($this->getParameter('file_directory').'/photos_profil',$fileName);

                }catch(FileException $e){
                    $this->addFlash('notice','Erreur lors de l\'insertion du fichier');
                }

                $this->addFlash('notice','Utilisateur modifié');
                return $this->redirectToRoute('user_profile');        
            }          
        }        
        return $this->render('utilisateur/user_profile.html.twig', [            
            'form'=>$form->createView(),          
            'user'=>$user   
        ]);
    }
}
