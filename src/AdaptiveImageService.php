<?php
namespace App;

use Imagine\Gd\Imagine;
use Wa72\AdaptImage\AdaptiveImageResizer;
use Wa72\AdaptImage\ImageFileInfo;
use Wa72\AdaptImage\ImageResizeDefinition;
use Wa72\AdaptImage\Output\OutputPathGeneratorBasedir;
use Wa72\AdaptImage\ThumbnailGenerator;

class AdaptiveImageService
{
    /**
     * @var ThumbnailGenerator
     */
    private $tg;
    /**
     * @var AdaptiveImageResizer
     */
    private $rs;

    /**
     * AdaptiveImageService constructor.
     * @param string $cache_dir
     */
    public function __construct(string $cache_dir)
    {
        $imagine = new Imagine();
        $output_path_namer = new OutputPathGeneratorBasedir($cache_dir);

        $this->tg = new ThumbnailGenerator($imagine, $output_path_namer, 300, 300, 'inset');
        $this->rs = new AdaptiveImageResizer($imagine, $output_path_namer, array(
            new ImageResizeDefinition(2048, 2048),
            new ImageResizeDefinition(1600, 1200),
            new ImageResizeDefinition(1280, 1024),
            new ImageResizeDefinition(768, 576),
            new ImageResizeDefinition(1024, 768),
        ));
    }

    /**
     * @param bool $really_do_it
     * @param ImageFileInfo $image
     * @return ImageFileInfo
     */
    public function thumbnail(bool $really_do_it, ImageFileInfo $image)
    {
        return $this->tg->thumbnail($really_do_it, $image);
    }

    /**
     * @param bool $really_do_it
     * @param ImageFileInfo $image
     * @param int $width
     * @return ImageFileInfo
     */
    public function resize(bool $really_do_it, ImageFileInfo $image, int $width)
    {
        return $this->rs->resize($really_do_it, $image, $width);
    }
}