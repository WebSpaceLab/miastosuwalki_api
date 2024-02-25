<?php
namespace App\Service;

use App\Repository\TeamRepository;
use Symfony\Component\Asset\Packages;

class TeamHelper
{
    public function __construct(private Packages $packages, private TeamRepository $teamRepository) {}

    public function getMonths() {
        // Wykonanie zapytania
        $dates = $this->teamRepository->getActiveMonths();
    
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

    public function getActive() {
        // Wykonanie zapytania
        $results =  $this->teamRepository->getActive();
        
        // Przetworzenie wyników
        $isActive = array_map(function($item) {
            return [
                'value' => $item['is_active'],
                'label' => ucfirst($item['is_active'])
            ];
        }, $results);
        
        // Usuwanie duplikatów - przy założeniu, że mime_type jest unikatowy, można użyć array_unique
        $isActive = array_values(array_unique($isActive, SORT_REGULAR));
    
        return $isActive;
    }
    
}