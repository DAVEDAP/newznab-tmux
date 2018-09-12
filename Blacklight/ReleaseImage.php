<?php

namespace Blacklight;

use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Exception\NotFoundException;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\Exception\NotWritableException;

/**
 * Resize/save/delete images to disk.
 *
 * Class ReleaseImage
 */
class ReleaseImage
{
    /**
     * Path to save ogg audio samples.
     *
     * @var string
     */
    public $audSavePath;

    /**
     * Path to save video preview jpg pictures.
     *
     * @var string
     */
    public $imgSavePath;

    /**
     * Path to save large jpg pictures(xxx).
     *
     * @var string
     */
    public $jpgSavePath;

    /**
     * Path to save movie jpg covers.
     *
     * @var string
     */
    public $movieImgSavePath;

    /**
     * Path to save video ogv files.
     *
     * @var string
     */
    public $vidSavePath;

    /**
     * ReleaseImage constructor.
     */
    public function __construct()
    {
        $this->audSavePath = NN_COVERS.'audiosample'.DS;
        $this->imgSavePath = NN_COVERS.'preview'.DS;
        $this->jpgSavePath = NN_COVERS.'sample'.DS;
        $this->movieImgSavePath = NN_COVERS.'movies'.DS;
        $this->vidSavePath = NN_COVERS.'video'.DS;
    }

    /**
     * @param        $imgLoc
     * @param string $token
     *
     * @return bool|\Intervention\Image\Image
     */
    protected function fetchImage($imgLoc, $token = '')
    {
        try {
            if ($token !== '') {
                $file_data = file_get_contents(
                    $imgLoc,
                    false,
                    stream_context_create(
                        [
                            'ssl' => [
                                'verify_peer'      => false,
                                'verify_peer_name' => false,
                            ],
                            'http' => [
                                'method' => 'GET',
                                'headers' => [
                                    'Accept-language' => 'en',
                                    'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.2 (KHTML, like Gecko) Chrome/22.0.1216.0 Safari/537.2',
                                    'Authorization'  => 'Bearer '.$token,
                                ],
                            ],
                        ]
                    )
                );
            } else {
                $file_data = $imgLoc;
            }
            $img = Image::make($file_data);
        } catch (NotFoundException $e) {
            if ($e->getCode() === 404) {
                ColorCLI::doEcho(ColorCLI::notice('Data not available on server'), true);
            } elseif ($e->getCode() === 503) {
                ColorCLI::doEcho(ColorCLI::notice('Service unavailable'), true);
            } else {
                ColorCLI::doEcho(ColorCLI::notice('Unable to fetch image: '.$e->getMessage()), true);
            }

            $img = false;
        } catch (NotReadableException $e) {
            ColorCLI::doEcho(ColorCLI::notice($e->getMessage()), true);

            $img = false;
        } catch (ImageException $e) {
            ColorCLI::doEcho(ColorCLI::notice('Image error: '.$e->getMessage()), true);

            $img = false;
        }

        return $img;
    }

    /**
     * Save an image to disk, optionally resizing it.
     *
     * @param string $imgName      What to name the new image.
     * @param string $imgLoc       URL or location on the disk the original image is in.
     * @param string $imgSavePath  Folder to save the new image in.
     * @param string $imgMaxWidth  Max width to resize image to.   (OPTIONAL)
     * @param string $imgMaxHeight Max height to resize image to.  (OPTIONAL)
     * @param bool   $saveThumb    Save a thumbnail of this image? (OPTIONAL)
     *
     * @param string $token
     *
     * @return int 1 on success, 0 on failure Used on site to check if there is an image.
     */
    public function saveImage($imgName, $imgLoc, $imgSavePath, $imgMaxWidth = '', $imgMaxHeight = '', $saveThumb = false, $token = ''): int
    {
        $cover = $this->fetchImage($imgLoc, $token);

        if ($cover === false) {
            return 0;
        }

        // Check if we need to resize it.
        if ($imgMaxWidth !== '' && $imgMaxHeight !== '') {
            $width = $cover->width();
            $height = $cover->height();
            $ratio = min($imgMaxHeight / $height, $imgMaxWidth / $width);
            // New dimensions
            $new_width = $ratio * $width;
            $new_height = $ratio * $height;
            if ($new_width < $width && $new_width > 10 && $new_height > 10) {
                $cover->resize($new_width, $new_height);

                if ($saveThumb) {
                    $cover->save($imgSavePath.$imgName.'_thumb.jpg');
                }
            }
        }
        // Store it on the hard drive.
        $coverPath = $imgSavePath.$imgName.'.jpg';
        try {
            $cover->save($coverPath);
        } catch (NotWritableException $e) {
            return 0;
        }
        // Check if it's on the drive.
        if (! File::isReadable($coverPath)) {
            return 0;
        }

        return 1;
    }

    /**
     * Delete images for the release.
     *
     * @param string $guid The GUID of the release.
     *
     * @return void
     */
    public function delete($guid): void
    {
        $thumb = $guid.'_thumb.jpg';

        Storage::delete([$this->audSavePath.$guid.'.ogg', $this->imgSavePath.$thumb, $this->jpgSavePath.$thumb, $this->vidSavePath.$guid.'.ogv']);
    }
}
