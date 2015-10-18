<?php

namespace Ddr\Component\Imagine;

use Imagine\Gd\Imagine;

abstract class ImageFactory
{
    public function fromStream($stream)
    {
        $imagine = new Imagine();
        $image = $imagine->load($stream);
        $size = $image->getSize();

        return new Image($image, $size);
    }
}
