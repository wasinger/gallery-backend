<?php
namespace App\Controller;

use App\AdaptiveImageService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wa72\Gallery\Gallery;

class DefaultController extends Controller
{

    /**
     * @var Gallery
     */
    private $gallery;
    /**
     * @var AdaptiveImageService
     */
    private $adi;

    /**
     * DefaultController constructor.
     * @param Gallery $gallery
     * @param AdaptiveImageService $adi
     */
    public function __construct(Gallery $gallery, AdaptiveImageService $adi)
    {
        $this->gallery = $gallery;
        $this->adi = $adi;
    }

    public function index(Request $request, $album) {
        if ($album) {
            $album = $this->gallery->getAlbum($album);
            $aa = [
                'name' => $album->getName(),
                'slug' => $album->getSlug(),
                'url' => $this->generateUrl('index', ['album' => $album->getSlug()]),
                'images' => []
            ];
            foreach ($album->getImages() as $image) {
                $thumbnail = $this->adi->thumbnail(false, $image->getFile());
                $resized_image = $this->adi->resize(false, $image->getFile(), 2048);
                $aa['images'][] = [
                    'slug' => $image->getSlug(),
                    'url' => $this->generateUrl('image', ['slug' => $image->getSlug()]),
                    'width' => $resized_image->getWidth(),
                    'height' => $resized_image->getHeight(),
                    'thumbnail' => [
                        'url' => $this->generateUrl('thumbnail', ['slug' => $image->getSlug()]),
                        'width' => $thumbnail->getWidth(),
                        'height' => $thumbnail->getHeight()
                    ]
                ];
            }
            return new JsonResponse($aa);
        } else { // list all albums
            $albums = [];
            foreach ($this->gallery->getAlbums() as $album) {
                $na = [
                    'name' => $album->getName(),
                    'slug' => $album->getSlug(),
                    'url' => $this->generateUrl('index', ['album' => $album->getSlug()])
                ];
                if (($thumbnailimage = $album->getThumbnailImage()) !== null) {
                    $na['thumbnail'] = [
                        'url' => $this->generateUrl('thumbnail', ['slug' => $thumbnailimage->getSlug()]),
                        'width' => $thumbnailimage->getWidth(),
                        'height' => $thumbnailimage->getHeight()
                    ];
                }
                $albums[] = $na;

            }
            return new JsonResponse($albums);
        }
    }
}