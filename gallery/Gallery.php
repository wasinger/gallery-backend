<?php
namespace Wa72\Gallery;

class Gallery {
    /** @var  Interfaces\GalleryBackend $backend */
    protected $backend;

    /** @var  string $name */
    protected $name;

    public function __construct(Interfaces\GalleryBackend $backend)
    {
        $this->backend = $backend;
    }

    /**
     * @return Interfaces\GalleryBackend
     */
    public function getBackend()
    {
        return $this->backend;
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
     * @param Album|null $album
     * @return Album[]
     */
    public function getAlbums($album = null)
    {
        return $this->backend->getAlbums($album);
    }

    /**
     * @param Album $album
     * @return Image[]
     */
    public function getImages(Album $album)
    {
        return $this->backend->getImages($album);
    }

    /**
     * @param string $slug
     * @return Album
     */
    public function getAlbum($slug)
    {
        return $this->backend->getAlbum($slug);
    }

    /**
     * @param string $slug
     * @return Image
     */
    public function getImage($slug)
    {
        return $this->backend->getImage($slug);
    }

    /**
     * @param string $slug
     * @return string
     */
    public function getFullPath($slug)
    {
        return $this->backend->getFullPath($slug);
    }
}