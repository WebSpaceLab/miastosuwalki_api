<?php
namespace App\Service;

use App\Repository\InboxRepository;
use Symfony\Component\Asset\Packages;

class InboxHelper
{
    public function __construct(private Packages $packages, private InboxRepository $inboxRepository) {}

    public function getMonths() {
        // Wykonanie zapytania
        $dates = $this->inboxRepository->getActiveMonths();
    
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

    public function getRead() {

        // Wykonanie zapytania
        $results =  $this->inboxRepository->getActiveIsRead();
        
        // Przetworzenie wyników
        $isRead = array_map(function($item) {
            return [
                'value' => $item['is_read'],
                'label' => ucfirst($item['is_read'])
            ];
        }, $results);
        
        // Usuwanie duplikatów - przy założeniu, że mime_type jest unikatowy, można użyć array_unique
        $isRead = array_values(array_unique($isRead, SORT_REGULAR));
    
        return $isRead;
    }
    
}