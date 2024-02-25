<?php
namespace App\Service;

use App\Repository\ArticleRepository;
use Symfony\Component\Asset\Packages;

class ArticleHelper
{
    public function __construct(private Packages $packages, private ArticleRepository $articleRepository) {}

    public function getMonths() {
        // Wykonanie zapytania
        $dates = $this->articleRepository->getActiveMonths();
    
        // Przetworzenie wyników w PHP
        $months = [];
        foreach ($dates as $dateArray) {
            $date = $dateArray['createdAt'];
            $months[] = [
                'value' => $date->format('01-m-Y'),
                'label' => $date->format('M Y')
            ];
        }
    
        // Usuwanie duplikatów
        $months = array_values(array_unique($months, SORT_REGULAR));
    
        return $months;
    }

    public function getPublished() {
        // Wykonanie zapytania
        $results =  $this->articleRepository->getPublished();
        
        // Przetworzenie wyników
        $isPublished = array_map(function($item) {
            return [
                'value' => $item['is_published'],
                'label' => ucfirst($item['is_published'])
            ];
        }, $results);
        
        // Usuwanie duplikatów - przy założeniu, że mime_type jest unikatowy, można użyć array_unique
        $isPublished = array_values(array_unique($isPublished, SORT_REGULAR));
    
        return $isPublished;
    }
    
}