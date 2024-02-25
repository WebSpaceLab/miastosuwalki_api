<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderHelper
{
    public function uploadImage(UploadedFile $uploadedFile, string $uploadsPath): string
    {
        $newFilename = $this->createdFileName($uploadedFile);

        $uploadedFile->move(
            $uploadsPath,
            $newFilename
        );

        return $newFilename;
    }

    public function createdFileName(UploadedFile $uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = $originalFilename  . '-' .md5(uniqid()) . '.' . $uploadedFile->guessExtension();

        return $newFilename;
    }

    public function getYoutubeId(string $videoId): string
    {
        return 'https://www.youtube.com/embed/' . $videoId;
    }

    public function selectUrlKeyUsingType(mixed $data): mixed
    {
        switch ($data['type']) {
            case "video/youTube":
                $data['movieUrl'] = $this->getYoutubeId($data['mediaUrl']);
                break;
            // case 'vimeo':
            //     $data['movieUrl'] = $uploaderHelper->getVimeoId($data['movieUrl']);
            //     break;
            default:
                $data['movieUrl'] = $data['mediaUrl'];
                break;
            
            return $data;
        }
    }
}