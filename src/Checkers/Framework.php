<?php

namespace codicastudio\Health\Checkers;

use codicastudio\Health\Support\Result;

class Framework extends Base
{
    /**
     * @return Result
     */
    public function check()
    {
        return $this->makeHealthyResult();
    }
}
