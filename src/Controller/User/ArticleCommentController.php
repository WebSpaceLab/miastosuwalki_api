<?php

namespace App\Controller\User;

use App\Controller\AbstractAPIController;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/user', name: 'app_user_article_comment')]
class ArticleCommentController extends AbstractAPIController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/articles/{id}/comment', name: ':create', methods: ['POST'])]
    public function create(Article $article, CommentRepository $commentRepository, ValidatorInterface $validator, Request $request, #[CurrentUser()] User $user = null): JsonResponse
    {
        $requestComment = $this->deserialize($request->getContent(), Comment::class, 'json');
        $errors = $validator->validate($requestComment);

        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $comment =  new Comment; 

        $comment->setArticle($article);
        $comment->setName($requestComment->getName());
        $comment->setContent($requestComment->getContent());
        $comment->setOwner($user);

        $commentRepository->save($comment, true);

        return $this->response($comment, ['comment:read'], Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/comments/{id}', name: ':show', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        // TODO ????????
        return $this->response($comment, ['comment:write']);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/comments/{id}', name: ':update', methods: ['PATCH'])]
    public function update(CommentRepository $commentRepository, Comment $comment, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $comment = $this->deserialize($request->getContent(), Comment::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $comment,
        ]);


        $errors = $validator->validate($comment);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $commentRepository->save($comment, true);

        return $this->response($comment, ['comment:read']);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/comments/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(CommentRepository $articleRepository, Comment $comment): JsonResponse
    {
        $articleRepository->remove($comment, true);
        return $this->response('', [], Response::HTTP_NO_CONTENT);
    }
}
