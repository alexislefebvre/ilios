<?php
namespace Ilios\CoreBundle\Controller;

use Ilios\CoreBundle\Classes\FileSystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Exception;
/**
 * Class UploadController
 * @package Ilios\CoreBundle\Controller
 */
class UploadController extends Controller
{

    public function uploadAction(Request $request)
    {
        $fs = $this->container->get('ilioscore.filesystem');
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            return new JsonResponse(array(
                'errors' => 'No parameter "file" was found in the request'
            ), JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!$uploadedFile->isValid()) {
            return new JsonResponse(array('errors' => 'File failed to upload'), JsonResponse::HTTP_BAD_REQUEST);
        }
        $hash = $fs->storeFile($uploadedFile);
        
        return new JsonResponse(array('fileHash' => $hash), JsonResponse::HTTP_OK);
    }
}
