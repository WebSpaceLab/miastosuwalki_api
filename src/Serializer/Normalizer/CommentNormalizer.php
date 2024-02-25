<?php

namespace App\Serializer\Normalizer;

use App\Repository\ArticleRepository;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CommentNormalizer extends ObjectNormalizer
{
    // public function __construct(
    //     private ArticleRepository $articleRepository,
    //     ?ClassMetadataFactoryInterface $classMetadataFactory = null,
    //     ?NameConverterInterface $nameConverter = null,
    //     ?PropertyAccessorInterface $propertyAccessor = null,
    //     ?PropertyTypeExtractorInterface $propertyTypeExtractor = null
    // )
    // {
    //     $this->articleRepository = $articleRepository;
    //     parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);
    // }

    // public function normalize($object, string $format = null, array $context = []): array
    // {
        
    //     $data['comment'] = parent::normalize($object, $format, $context);
    //     // TODO: add, edit, or delete some data
    //     $data['articleId'] = $object->getArticle()->getId() ? $object->getArticle()->getId() : null;

    //     return $data;
    // }

    
    // public function supportsNormalization($data, string $format = null, array $context = []): bool
    // {
    //     return $data instanceof \App\Entity\Comment;
    // }
    
    // public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed
    // {
    //     $comment = parent::denormalize($data, $type, $format, $context);

    //     if (empty($data['articleId'])) {
    //         return $comment;
    //     }

    //     $article = $this->articleRepository->find($data['articleId']);
    //     if ($article) {
    //         $comment->setArticle($article);
    //     }

    //     return $comment;
    // }


    // public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    // {
    //     return $type == \App\Entity\Comment::class;
    // }

}
