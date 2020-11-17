<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Theme;
use App\Form\AjoutThemeType;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ThemeRepository;
use App\Form\ModifThemeType;

class ThemeController extends AbstractController
{
    /**
     * @Route("/ajoutTheme", name="ajoutTheme")
     */
    public function ajoutTheme(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $theme = new Theme();
        $form = $this->createForm(AjoutThemeType::class, $theme); 
        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($theme);
                $em->flush();
                $this->addFlash('notice','Thème inséré');
                return $this->redirectToRoute('ajoutTheme');        
            }          
        }        
        return $this->render('theme/ajoutTheme.html.twig', [            
            'form'=>$form->createView()        
        ]);
    }

    /**
     * @Route("/liste_themes", name="liste_themes")
     */
    public function liste_themes(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $em = $this->getDoctrine();
        $repoTheme = $em->getRepository(Theme::class);

        if($request->get('supp')!=null){
            $theme = $repoTheme->find($request->get('supp'));
            if($theme!=null){
                $em->getManager()->remove($theme);
                $em->getManager()->flush();
            }
            $this->redirectToRoute('liste_themes');
        }

        $themes = $repoTheme->findBy(array(), array('libelle'=>'ASC'));
        return $this->render('theme/liste_themes.html.twig', [            
            'themes'=>$themes
        ]);
    }

    /**
     * @Route("/modif_theme/{id}", name="modif_theme", requirements={"id"="\d+"})
     */
    public function modif_theme(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine();
        $repoTheme = $em->getRepository(Theme::class);
        $theme = $repoTheme->find($id);

        if($theme==null){
            $this->addFlash('notice','Cette page n\'existe pas');
            return $this->redirectToRoute('liste_themes');   
        }

        $form = $this->createForm(ModifThemeType::class,$theme);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);            
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($theme);
                $em->flush();
                $this->addFlash('notice','Thème modifié');
                return $this->redirectToRoute('liste_themes');        
            }          
        } 

        return $this->render('theme/modif_theme.html.twig', [            
            'form'=>$form->createView()        
        ]);
    }

    
}
