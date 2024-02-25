<?php

namespace App\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationHelper
{
    public function paginate($query, $pageNumber = 1, $perPage = 10)
    {
        $page = $pageNumber < 1 ? 1 : $pageNumber;

        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult(($page-1) * $perPage)
            ->setMaxResults($perPage);

        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $perPage);

        return [
            'data' => $paginator,
            'pagination' => [
                'total' => $totalItems,
                'current_page' => $page,
                'per_page' => $perPage,
                'pages_count' => $pagesCount,
            ],
        ];
    }
}