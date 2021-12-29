<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'comment')]
    public function index(): Response
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }




      /**
     * @Route("/comment/{id}", name="comment_delete", methods={"POST"})
     */
    public function delete(Request $request, Comment $comment, EntityManagerInterface $em): Response
    {
        $episode = $comment->getEpisode()->getSlug();

        if(($this->getUser() == $comment->getAuthor()) || in_array("ROLE_ADMIN",$this->getUser()->getRoles())){
            if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
                $em->remove($comment);
                $em->flush();
            }
        }

        return $this->redirectToRoute('episode_show', ["slug" => $episode], Response::HTTP_SEE_OTHER);
    }
}
