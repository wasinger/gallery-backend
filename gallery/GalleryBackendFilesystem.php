<?php
namespace Wa72\Gallery;
use PHPExif\Adapter\Native;
use PHPExif\Reader;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Wa72\Gallery\Interfaces\EditableGalleryBackend;
use Wa72\Gallery\Interfaces\GalleryBackend;

class GalleryBackendFilesystem implements GalleryBackend, EditableGalleryBackend
{
    protected $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param Album|null $album
     * @return Album[]
     */
    public function getAlbums($album = null)
    {
        $finder = new Finder();
        $parent_slug = ($album instanceof Album ? $album->getSlug() : '');
        $dir = ($parent_slug ? $this->getPathForSlug($parent_slug) : $this->directory);
        $finder->directories()->in($dir)->depth('== 0')->sortByName();
        $albums = array();
        foreach ($finder as $dir) {
            /** @var \Symfony\Component\Finder\SplFileInfo $dir */
            $a = new Album($this, ($parent_slug ? $parent_slug . '/' : '') . $dir->getFilename());
            $md = $this->readAlbumMetadata($a);
            $a->setName($md['title']);
            $albums[] = $a;
        }
        return $albums;
    }

    /**
     * @param Album $album
     * @return Image[]
     */
    public function getImages(Album $album)
    {
        $finder = new Finder();
        $dir = $this->getPathForSlug($album->getSlug());
        if (!is_readable($dir)) {
            return [];
        }
        $finder->files()->in($dir)->depth('== 0')->name('/\.jpe?g$/i')->sortByName();
        //$finder->files()->in($this->directory)->path($album->getSlug())->name('/\.jpe?g$/i')->depth('== 0')->sortByName();
        /** @var Image[] $imagefiles */
        $imagefiles = array();
        foreach ($finder as $file) {
            $slug = $album->getSlug() . '/' . $file->getFilename();
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $p = new Image($this, $slug);
            $imagefiles[$slug] = $p;
        }
        $md = $this->readAlbumMetadata($album);
        $images = array();
        if (isset($md['images']) && is_array($md['images'])) {
            foreach ($md['images'] as $i) {
                if (isset($imagefiles[$slug])) {
                    $imagefiles[$slug]->setTitle($i['title']);
                    $images[$slug] = $imagefiles[$slug];
                    unset($imagefiles[$slug]);
                }
            }
        }
        if (count($imagefiles)) {
            foreach ($imagefiles as $image) {
                $exif = $this->getExifData($image->getFile()->getPathname());
                if ($caption = utf8_encode($exif->getCaption())) {
                    $image->setTitle($caption);
                }
                $images[$image->getSlug()] = $image;
            }
            $album->setImages($images);
            $this->saveAlbumMetadata($album);
        }
        return $images;
    }

    public function getFirstImage(Album $album)
    {
//        $finder = new Finder();
        $dir = $this->getPathForSlug($album->getSlug());
        if (!is_readable($dir)) {
            return false;
        }
//        $finder->files()->in($dir)->depth('== 0')->name('/\.jpe?g$/i')->sortByName();
//        if (iterator_count($finder)) {
//            $file = iterator_to_array($finder->getIterator(), false)[0];
//            $slug = $album->getSlug() . '/' . $file->getFilename();
//            /** @var \Symfony\Component\Finder\SplFileInfo $file */
//            return new Image($this, $slug);
//        } else {
//            return null;
//        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if (substr($file, -4) == '.jpg'
                || substr($file, -4) == '.JPG'
                || substr($file, -5) == '.jpeg'
                || substr($file, -5) == '.JPEG'
            ) {
                $slug = $album->getSlug() . '/' . $file;
                return new Image($this, $slug);
            }
        }
    }


    /**
     * @param Album|null $album The parent album, null for root level albums
     * @param $sort_order Array of album IDs in the desired order
     * @return void
     */
    public function sortAlbums($album, $sort_order)
    {
        // TODO: Implement sortAlbums() method.
    }

    /**
     * @param Album $album
     * @param array $sort_order Array of image IDs in the desired order
     * @return void
     */
    public function sortImages($album, $sort_order)
    {
        // TODO: Implement sortImages() method.
    }

    /**
     * @param string $slug
     * @return Album
     */
    public function getAlbum($slug)
    {
        $album = new Album($this, $slug);
        $md = $this->readAlbumMetadata($album);
        $album->setName($md['title']);
        return $album;
    }

    /**
     * @param string $slug
     * @return Image
     */
    public function getImage($slug)
    {
        $photo = new Image($this, $slug);
        $album_slug = dirname($slug);
        $album = new Album($this, $album_slug);
        $md = $this->readAlbumMetadata($album);
        if (isset($md['images']) && is_array($md['images'])) {
            foreach ($md['images'] as $i) {
                if ($i['slug'] == $slug) {
                    $photo->setTitle($i['title']);
                    break;
                }
            }
        }
        return $photo;
    }

    protected function getPathForSlug($slug)
    {
        $fullpath = $this->directory . '/' . $slug;
        //if (!(file_exists($fullpath))) throw new FileNotFoundException($fullpath);
        return $fullpath;
    }

    /**
     * Return the full file system path for an image file by its slug
     *
     * @param string $image_slug
     * @return string
     */
    public function getFullPath($image_slug)
    {
        return $this->getPathForSlug($image_slug);
    }

    /**
     * @param $title
     * @param Album|null $parent
     * @return Album
     */
    public function createAlbum($title, $parent = null)
    {
        $slug = self::slugify($title);
        if ($parent instanceof Album) {
            $slug = $parent->getSlug() . '/' . $slug;
        }
        $dir = $this->getPathForSlug($slug);
        mkdir($dir);
        $album = new Album($this, $slug);
        $album->setName($title);
        $this->saveAlbumMetadata($album);
        return $album;
    }

    public function saveAlbum(Album $album)
    {
        $this->saveAlbumMetadata($album);
    }

    /**
     * Save modified image data to the persistance backend
     *
     * @param Image $image
     * @return void
     */
    public function saveImage(Image $image)
    {
        $album = $this->getAlbum(dirname($image->getSlug()));
        $this->saveAlbumMetadata($album);
    }


    public function deleteAlbum(Album $album)
    {
        // TODO: Implement deleteAlbum() method.
    }

    public function deleteImage(Image $image)
    {
        // TODO: Implement deleteImage() method.
    }

    /**
     * Import an image file into the gallery
     *
     * @param \SplFileInfo $imagefile The image file to import
     * @param Album $album The album into which the image is to be imported
     * @return Image
     */
    public function importImageFromFile(\SplFileInfo $imagefile, Album $album)
    {
        $albumpath = $this->getPathForSlug($album->getSlug());
        if ($imagefile->getPath() != $albumpath) {
            // copy the image file into the album dir if it isn't already there
            copy($imagefile->getPathname(), $albumpath . DIRECTORY_SEPARATOR . $imagefile->getFilename());
        }
        $imageslug = $album->getSlug() . '/' . $imagefile->getFilename();
        $image = new Image($this, $imageslug);
        $exifdata = $this->getExifData($image->getFile()->getPathname());
        $image->setTitle(utf8_encode($exifdata->getCaption()));
        $this->saveImage($image);
    }

    /**
     * @param string $pathname
     * @return \PHPExif\Exif
     */
    protected function getExifData($pathname)
    {
        $reader = new Reader\Reader(new Native());
        return $reader->getExifFromFile($pathname);
    }

    /**
     * @param Album $album
     * @return array
     */
    protected function readAlbumMetadata(Album $album)
    {
        $md = array();
        $dir = $this->getPathForSlug($album->getSlug());
        if (file_exists($dir . DIRECTORY_SEPARATOR . '.album.xml')) {
            $xmldata = @file_get_contents($dir . DIRECTORY_SEPARATOR . '.album.xml');
            try {
                $xml = new \SimpleXMLElement($xmldata);
                $md['title'] = (string) $xml['title'];
                $images = $xml->xpath('//image');
                foreach ($images as $xi) {
                    $md['images'][] = array(
                        'slug' => (string) $xi['slug'],
                        'title' => (string) $xi['title']
                    );
                }
            } catch (\Exception $e) {

            }
        } else {
            $md['title'] = basename($album->getSlug());
        }
        return $md;
    }

    protected function saveAlbumMetadata(Album $album)
    {
        $dir = $this->getPathForSlug($album->getSlug());
        $xmldata = '<?xml version="1.0" standalone="yes"?><album></album>';
        $xml = new \SimpleXMLElement($xmldata);
        $xml->addAttribute('title', $album->getName());
        foreach ($album->getImages() as $image) {
            $xi = $xml->addChild('image');
            $xi->addAttribute('slug', $image->getSlug());
            $xi->addAttribute('title', $image->getTitle());
        }
//        try {
//            $xml->asXML($dir . DIRECTORY_SEPARATOR . '.album.xml');
//        } catch (\Exception $e) {
//
//        }
    }

    static public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }


}