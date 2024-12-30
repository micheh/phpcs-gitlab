<?php

namespace DoNotUse;

use function min;

class Multiple
{
    public function __invoke(): string
    {
        min( 1, 2);
        min( 1, 2);

        return 'multiple';
    }
}
