<?php
namespace Wa72\Gallery;


use Wa72\Gallery\Interfaces\GalleryBackend;

class Album {

    /** @var  string $name */
    protected $name;

    /** @var  string $slug */
    protected $slug;

    /** @var  GalleryBackend $backend */
    protected $backend;

    /**
     * @var Image[]
     */
    protected $images;

    /**
     * @var Album[]
     */
    protected $albums;

    /**
     * @var Image
     */
    protected $thumbnail_image;



    public function __construct(GalleryBackend $backend, $slug)
    {
        $this->backend = $backend;
        $this->slug = $slug;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return Album[]
     */
    public function getAlbums()
    {
        if (!count($this->albums)) {
            $this->albums = $this->backend->getAlbums($this);
        }
        return $this->albums;
    }

    /**
     * @return Image[]
     */
    public function getImages()
    {
        if (!count($this->images)) {
            $this->images = $this->backend->getImages($this);
        }
        return $this->images;
    }

    /**
     * @param string $slug
     */
    public function getImage($slug)
    {

    }

    /**
     * @param Image[] $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @param Image $image
     */
    public function setThumbnailImage(Image $image)
    {
        $this->thumbnail_image = $image;
    }

    /**
     * @return Image|null
     */
    public function getThumbnailImage()
    {
        if ($this->thumbnail_image) {
            return $this->thumbnail_image;
        } else {
//            if ($c = count($this->getImages())) {
//                $ak = array_keys($this->images);
//               return $this->images[$ak[rand(0, $c - 1)]]; // random image, disabled for performance reasons
//            } else {
//                return null;
//            }
            return $this->backend->getFirstImage($this);
        }
    }
}