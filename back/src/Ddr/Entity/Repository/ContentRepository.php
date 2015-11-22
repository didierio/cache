<?php

namespace Ddr\Entity\Repository;

use Ddr\Entity\Content;

class ContentRepository implements RepositoryInterface
{
    public function since(Content $content = null)
    {
        $qb = $this->createQueryBuilder('c');

        if ()
        $qb
            ->setLimit(10);
            ->getQuery()
            ->getResults()
        ;
    }
}
