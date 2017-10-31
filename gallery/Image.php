<?php

namespace Wa72\Gallery;


use PHPExif\Reader;
use Wa72\AdaptImage\ImageFileInfo;
use Wa72\Gallery\Imager\Ratio;
use Wa72\Gallery\Interfaces\GalleryBackend;

class Image {
    /**
     * @var string
     */
    protected $title;
    /**
     * @var int
     */
    protected $width;
    /**
     * @var int
     */
    protected $height;

    /**
     * @var GalleryBackend
     */
    protected $backend;

    protected $slug;

    protected $thumbnailCropCoordinates = array();

    /**
     * @var ImageFileInfo
     */
    protected $file;

    public function __construct(GalleryBackend $backend, $slug)
    {
        $this->backend = $backend;
        $this->slug = $slug;
        $fullpath = $backend->getFullPath($slug);
        $this->file = ImageFileInfo::createFromFile($fullpath);
        $this->width = $this->file->getWidth();
        $this->height = $this->file->getHeight();
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title ?: $this->file->getFilename();
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    public function getRatio()
    {
        return new Ratio($this->width, $this->height);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return ImageFileInfo
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param Ratio $ratio
     * @return array|null
     */
    public function getThumbnailCropCoordinatesForRatio(Ratio $ratio)
    {
        $ratio = $ratio->__toString();
        if (isset($this->thumbnailCropCoordinates[$ratio])) {
            return $this->thumbnailCropCoordinates[$ratio];
        }
        return null;
    }

    /**
     * @param Ratio $ratio
     * @param int $x
     * @param int $y
     * @param int $w
     * @param int $h
     */
    public function setThumbnailCropCoordinatesForRatio(Ratio $ratio, $x, $y, $w, $h) {
        $this->thumbnailCropCoordinates[$ratio->__toString()] = array($x, $y, $w, $h);
    }
} 