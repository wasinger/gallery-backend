<?php
namespace Wa72\Gallery\Interfaces;

use Wa72\Gallery\Album;
use Wa72\Gallery\Image;


interface GalleryBackend {
    /**
     * @param Album|null $album The parent album, null for root level albums
     * @return Album[]
     */
    public function getAlbums($album = null);

    /**
     * @param Album $album
     * @return Image[]
     */
    public function getImages(Album $album);

    /**
     * @param Album $album
     * @return Image|null
     */
    public function getFirstImage(Album $album);

    /**
     * @param string $slug
     * @return Album
     */
    public function getAlbum($slug);

    /**
     * @param string $slug
     * @return Image
     */
    public function getImage($slug);

    /**
     * Return the full file system path for a photo file by its slug
     *
     * @param string $image_slug
     * @return string
     */
    public function getFullPath($image_slug);

}