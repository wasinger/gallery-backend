<?php
namespace Wa72\Gallery\Interfaces;


use Wa72\Gallery\Album;
use Wa72\Gallery\Image;

interface EditableGalleryBackend extends GalleryBackend {
    /**
     * @param $title
     * @param Album|null $parent
     * @return Album the newly created album
     */
    public function createAlbum($title, $parent = null);

    /**
     * Save a modified album to the persistance backend
     *
     * @param Album $album
     * @return void
     */
    public function saveAlbum(Album $album);

    /**
     * Save modified image data to the persistance backend
     *
     * @param Image $image
     * @return void
     */
    public function saveImage(Image $image);

    /**
     * Set the sorting order for (sub)albums
     *
     * @param Album|null $album The parent album, null for root level albums
     * @param $sort_order Array of album identifiers in the desired order
     * @return void
     */
    public function sortAlbums($album, $sort_order);

    /**
     * Set the sorting order for images within an album
     *
     * @param Album $album
     * @param array $sort_order Array of image identifiers in the desired order
     * @return void
     */
    public function sortImages($album, $sort_order);

    /**
     * @param Album $album
     * @return void
     */
    public function deleteAlbum(Album $album);

    /**
     * @param Image $image
     * @return void
     */
    public function deleteImage(Image $image);

    /**
     * Import an image file into the gallery
     *
     * @param \SplFileInfo $imagefile The image file to import
     * @param Album $album The album into which the image is to be imported
     * @return Image
     */
    public function importImageFromFile(\SplFileInfo $imagefile, Album $album);


}