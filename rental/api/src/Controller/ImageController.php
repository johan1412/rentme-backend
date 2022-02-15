<?php


namespace App\Controller;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DomCrawler\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Vich\UploaderBundle\Handler\DownloadHandler;

class ImageController extends AbstractController
{
    public function downloadImageAction(Image $image, DownloadHandler $downloadHandler): Response
    {
        return $downloadHandler->downloadObject($image, $fileField = 'imageFile', $objectClass = null, $fileName = null, $forceDownload = false);
    }
}