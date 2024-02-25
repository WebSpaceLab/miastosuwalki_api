<?php
namespace App\Service;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Symfony\Component\Asset\Packages;

class MediaHelper
{
    protected static $types = [
        'image' => [
            'image/gif',
            'image/avif',
            'image/apng',
            'image/png',
            'image/svg+xml',
            'image/webp',
            'image/jpeg'
        ],
        'audio' => [
            'audio/mpeg',
            'audio/aac',
            'audio/wav',
        ],
        'video' => [
            'video/mp4',
            'video/webm',
            'video/mpeg',
            'video/x-msvideo',
            'video/youTube'
        ],
        'document' => [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/pdf'
        ],
        'archive' => [
            'application/zip',
            'application/x-7z-compressed',
            'application/gzip',
            'application/vnd.rar',
        ],
    ];

    public function __construct(private Packages $packages, private MediaRepository $mediaRepository) {}

    public function getFileType($media)
    {
        foreach (self::$types as $type => $mimes) {
            if (in_array($media->getMimeType(), $mimes)) {
                return $type;
            }
        }

        return 'other';
    }

    public function getPreviewUrlType(Media $media)
    {
        $urls = [
            'image' => $media->getPreviewUrl(),
            'audio' => $this->packages->getUrl('images/file-type-audio.svg'),
            'video' => $media->getPreviewUrl(),
            'document' => $this->packages->getUrl('images/file-type-document.svg'),
            'archive' => $this->packages->getUrl('images/file-type-archive.svg'),
            'other' => $this->packages->getUrl('images/file-type-other.svg'),
        ];

        return $urls[$this->getFileType($media)] ?? null;
    }

    public function changePreviewUrlsType($queryBuilder): void
    {
        /// Get the results as objects
        $results = $queryBuilder->getQuery()->getResult();

        // Modify the results
        foreach($results as $media) {
           $this->changePreviewUrlType($media);
        }
    }

    public function changePreviewUrlType($media): void
    {
        if($this->checkMimeTypeBelongsToFile($media)) {
           $media->setPreviewUrl($this->getPreviewUrlType($media));
        }
    }

    public static function getMimes($fileType)
    {
        return self::$types[$fileType] ?? [];
    }

    public function getFileTypes() {

        // Wykonanie zapytania
        $results =  $this->mediaRepository->getActiveMimeTypes();
        
        // Przetworzenie wyników
        $fileTypes = array_map(function($item) {
            return [
                'value' => $item['mime_type'],
                'label' => ucfirst($item['mime_type'])
            ];
        }, $results);
        
        // Usuwanie duplikatów - przy założeniu, że mime_type jest unikatowy, można użyć array_unique
        $fileTypes = array_values(array_unique($fileTypes, SORT_REGULAR));
    
        return $fileTypes;
    }

    public function getMonths() {
        // Wykonanie zapytania
        $dates = $this->mediaRepository->getActiveMonths();
    
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

    private function checkMimeTypeBelongsToFile($media): string
    {
        return  $this->getFileType($media) === 'image' || $this->getFileType($media) === 'video' ? false : true;
    }
}