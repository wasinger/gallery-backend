<?php
namespace Wa72\Gallery\Interfaces;

use Wa72\Gallery\Album;
use Wa72\Gallery\Image;

interface GalleryUrlCreator {
    /**
     * @param Album|string|int $album
     * @param bool $absolute
     * @return string
     */
    public function urlForAlbum($album, $absolute = false);

    /**
     * @param Image|string|int $image
     * @param Album|string|int|null $album
     * @param bool $absolute
     * @return string
     */
    public function urlForImage($image, $album = null, $absolute = false);
}