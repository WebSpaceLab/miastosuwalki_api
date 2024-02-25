<?php

namespace App\Controller\Editor;

use App\Controller\AbstractAPIController;
use App\Entity\Article;
use App\Entity\Gallery;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\GalleryRepository;
use App\Repository\MediaRepository;
use App\Service\ArticleHelper;
use App\Service\PaginationHelper;
use App\Service\QueryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/editor/articles', name: 'app_editor_articles')]
class ArticleController extends AbstractAPIController
{
    public function __construct(
        private PaginationHelper $paginationHelper, 
        private QueryHelper $QueryHelper,
        private ArticleHelper $articleHelper
    ) {}

    #[IsGranted('ROLE_EDITOR')]
    #[Route('', name: ':index', methods: ['GET'])]
    public function index(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $query = $request->query->all();

        $queryBuilder = $articleRepository->getWithSearchQueryBuilder(
            $query['term'], $query['orderBy'], $query['orderDir'], $query['status'], $query['month']
        );

        $pagination = $this->paginationHelper->paginate($queryBuilder, $query['page'], $query['per_page']);

        return $this->response([
            'articles' => $pagination['data'],
            'pagination' => $pagination['pagination'],
            'queryParams' =>  $this->QueryHelper->params($request, ['term','status','month']),
            'status' => $this->articleHelper->getPublished(),
            'months' => $this->articleHelper->getMonths(),
        ], ['admin:article:read']);
    }

    #[IsGranted('ROLE_EDITOR')]
    #[Route('', name: ':create', methods: ['POST'])]
    public function create(ArticleRepository $articleRepository, CategoryRepository $categoryRepository, MediaRepository $mediaRepository, GalleryRepository $galleryRepository, EntityManagerInterface $entityManager, ValidatorInterface $validator, Request $request): JsonResponse
    {
        $data = $request->toArray();
        $article = $this->deserialize($request->getContent(), Article::class, 'json', []);

        $violations = $validator->validate($article);
        
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $propertyPath = trim($violation->getPropertyPath(), '[\]');
                $errors[$propertyPath] = $violation->getMessage();
            }

            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $category = $categoryRepository->findOneBy(['id' => $data['categoryId']]);
        $media = $mediaRepository->findOneBy(['id' => $data['mediaId']]);

        $article->setAuthor($this->getUser());
        // $article->setSlug($article->getSlug() . '-' . uniqid());
        $article->setCategory($category);
        $article->setMedia($media);

        $article->setIsPublished($data['isPublished'] ?? false);

        if($data['galleries']) {
            $gallery = new Gallery();
            $gallery->setIsPublished(false);
            $gallery->setAuthor($this->getUser());
            $gallery->setSlug($article->getSlug() . '-' . uniqid());
            $gallery->setTitle($article->getTitle());
            $galleryRepository->save($gallery, true);
            $article->setGallery($gallery);

            foreach ($data['galleries'] as $medium) {
                $media = $mediaRepository->findOneBy(['id' => $medium['id']]);
                $gallery->addMedium($media);
                $entityManager->persist($media);
                $entityManager->persist($gallery);
                $entityManager->flush();
            }
        }

       

        $articleRepository->save($article, true);

        $this->flash('Artykuł został utworzony.');

        return $this->response([
            'article' => $article,
        ],['article:write'], Response::HTTP_CREATED);
    }


    #[IsGranted('ROLE_EDITOR')]
    #[Route('/{slug}', name: '.show', methods: ['GET'])]
    public function show(Article $article): JsonResponse
    {
        return $this->response([
            'article' => $article,
        ], ['admin:article:read']);
    }

    #[IsGranted('ROLE_EDITOR')]
    #[Route('/{id}', name: ':update', methods: ['PATCH'])]
    public function update(ArticleRepository $articleRepository, Article $article, ValidatorInterface $validator, Request $request, CategoryRepository $categoryRepository, MediaRepository $mediaRepository, GalleryRepository $galleryRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = $request->toArray();
        $article = $this->deserialize($request->getContent(), Article::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $article,
        ]);

        $errors = $validator->validate($article);
        
        if(count($errors) > 0) {
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $category = $categoryRepository->findOneBy(['id' => $data['categoryId']]);
        $media = $mediaRepository->findOneBy(['id' => $data['mediaId']]);

        $article->setCategory($category);
        $article->setMedia($media);

        if($data['galleries']) {
            $gallery = $galleryRepository->findOneBy(['id' => $article->getGallery()->getId()]);

            foreach ($data['galleries'] as $medium) {
                $media = $mediaRepository->findOneBy(['id' => $medium['id']]);
                $gallery->addMedium($media);
                $entityManager->persist($media);
                $entityManager->persist($gallery);
                $entityManager->flush();
            }
        }

        $articleRepository->save($article, true);

        $this->flash('Artykuł został zaktualizowany.');
        
        return $this->response([
            'article' => $article,
        ], ['article:write']);
    }

    #[IsGranted('ROLE_EDITOR')]
    #[Route('/{id}', name: ':delete', methods: ['DELETE'])]
    public function delete(ArticleRepository $articleRepository, Article $article): JsonResponse
    {
        $articleRepository->remove($article, true);
        
        return $this->response('', [], Response::HTTP_NO_CONTENT);
    }
}
