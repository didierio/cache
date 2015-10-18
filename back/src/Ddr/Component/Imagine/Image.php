<?php

namespace Ddr\Component\Imagine;

class Image
{
    public $image;
    public $box;

    public function __construct($image, $box)
    {
        $this->image = $image;
        $this->box = $box;
    }
}
