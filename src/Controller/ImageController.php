<?php
namespace App\Controller;


use App\AdaptiveImageService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Wa72\Gallery\Gallery;

class ImageController {
    /**
     * @var Gallery
     */
    protected $gallery;
    private $adi;
    protected $thumbnail_generator;

    public function __construct(Gallery $gallery, AdaptiveImageService $adi)
    {
        $this->gallery = $gallery;
        $this->adi = $adi;
    }

    /**
     * @param Request $request
     * @param $slug
     * @return BinaryFileResponse
     */
    public function showImage(Request $request, $slug)
    {
        $image = $this->gallery->getImage($slug);
        $rs = $this->adi->resize(true, $image->getFile(), '2000');
        $response = new BinaryFileResponse($rs->getPathname(), 200, array(), true, null, true, true);
        $response->prepare($request)->isNotModified($request);
        return $response;
    }

    /**
     * @param Request $request
     * @param $slug
     * @return BinaryFileResponse
     */
    public function showThumbnail(Request $request, $slug)
    {
        $image = $this->gallery->getImage($slug);
        $tn = $this->adi->thumbnail(true, $image->getFile());
        $response = new BinaryFileResponse($tn->getPathname(), 200, array(), true, null, true, true);
        $response->prepare($request)->isNotModified($request);
        return $response;
    }
}