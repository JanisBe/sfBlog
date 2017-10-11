<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use AppBundle\Form\CommentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $qb = $this->getDoctrine()
            ->getManager()
            ->createQueryBuilder()
            ->from('AppBundle:Post', 'p')
            ->select('p');

        $paginator = $this->get('knp_paginator');
        $pagination =  $paginator->paginate(
            $qb,
            $request->query->get('page',1),
            20
        );
        return $this->render('default/index.html.twig', [
            'posts' => $pagination,
        ]);
    }

    /**
     * @Route("/article/{id}",name = "post_show")
     */
    public function showAction(Post $post, Request $request){
        $comment= new Comment();
        $comment->setPost($post);

        if ($user = $this->getUser()){
            $comment->setUser($user);
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            $this->addFlash('success',"Kometarz dodany");

            return $this->redirectToRoute('post_show',['id' => $post->getId()]);
        }
        return $this->render('default/show.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }
}
