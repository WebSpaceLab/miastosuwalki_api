<?php
namespace App\Service;

use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Symfony\Component\Asset\Packages;

class UsersHelper
{
    public function __construct(private Packages $packages, private UserRepository $userRepository) {}

    public function getMonths() {
        // Wykonanie zapytania
        $dates = $this->userRepository->getActiveMonths();
    
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
}