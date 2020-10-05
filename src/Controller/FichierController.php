<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AjoutFichierType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Fichier;

class FichierController extends AbstractController
{
    /**
     * @Route("/ajout_fichier", name="ajout_fichier")
     */
    public function ajout_fichier(Request $request)
    {
        $fichier = new Fichier();
        $form = $this->createFormBuilder($fichier)
        ->add('utilisateur', EntityType::class, array('class' => 'App\Entity\Utilisateur', 'choice_label' => 'nom'))
        ->add('nom', FileType::class, array('label' => 'Fichier à télécharger'))
        ->add('ajouter', SubmitType::class, array('label' => 'Ajouter'))->getForm();
        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $file = $fichier->getNom();
                $fichier->setDate(new \DateTime());
                $fichier->setExtension($file->guessExtension()); //On récupère l'extension
                $fichier->setTaille($file->getSize());
                $em->persist($fichier);
                $em->flush();
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

                try{
                    $file->move($this->getParameter('file_directory'),$fileName);

                }catch(FileException $e){
                    $this->addFlash('notice','Erreur lors de l\'insertion du fichier');
                }

                $this->addFlash('notice','Fichier inséré');
                return $this->redirectToRoute('ajout_fichier');        
            }          
        }
        return $this->render('fichier/ajout_fichier.html.twig', [
            'form'=>$form->createView() 
        ]);
    }

    /**     
     * @return string     
     */    
    
    private function generateUniqueFileName()    
    {        
        return md5(uniqid());    
    }

    /**
     * @Route("/liste_fichiers", name="liste_fichiers")
     */
    public function liste_fichiers(Request $request)
    {
        $em = $this->getDoctrine();
        $repoFichier = $em->getRepository(Fichier::class);
        
        $fichier = new Fichier();
        $fichiers = $repoFichier->findBy(array(), array('nom'=>'ASC'));
        $file = $fichier->getNom();
        $fichiers['vrai_nom'] = $file->getClientOriginalName();
        return $this->render('fichier/liste_fichiers.html.twig', [            
            'fichiers'=>$fichiers
        ]);
    }
}
