<?php

namespace Ddr\Component\Imagine;

use Imagine\Image\ImageInterface;
use Imagine\Image\BoxInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;

class ImageOptimizer
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function optimize($stream, $extension)
    {
        $image = $this->prepare($stream);

        return $this->get($image->image, $extension);
    }

    public function thumbnail($stream, $extension, $width = null, $height = null)
    {
        if (null === $width) {
            $width = $this->config['thumbnail']['width'];
        }

        if (null === $height) {
            $height = $this->config['thumbnail']['height'];
        }

        $size = new Box($width, $height);
        $image = $this->prepare($stream);

        if ($image->box->getHeight() > $image->box->getWidth()) {
            $height = ($image->box->getHeight() / $image->box->getWidth()) * $size->getWidth();
            $resize = new Box($size->getWidth(), $height);
        } else {
            $resize = $size;
        }

        $stream = $this->resize($stream, $extension, $resize->getWidth(), $resize->getHeight(), false);
        $image = $this->prepare($stream);
        $x = (int) ($image->box->getWidth() - $size->getWidth()) / 2;
        $y = (int) ($image->box->getHeight() - $size->getHeight()) / 2;

        $point = new Point($x, $y);

        return $image->image->crop($point, $size)->get($extension);
    }

    public function resize($stream, $extension, $width = null, $height = null, $remake = true)
    {
        $image = $this->prepare($stream);
        $resize = false;
        if (null !== $height && $height < $image->box->getHeight()) {
            $size = $image->box->heighten($height);
            $resize = true;
        }

        if (null !== $width && $width < $image->box->getWidth() && $remake) {
            $size = $image->box->widen($width);
            $resize = true;
        }

        if ($resize) {
            return $image->image->resize($size)->get($extension);
        }

        return $image->image->get($extension);
    }

    public function get(ImageInterface $image, $extension)
    {
        $config['quality'] = $this->config['quality'];

        return $image->get($extension);
    }

    protected function prepare($stream)
    {
        return ImageFactory::fromStream($stream);
    }
}
