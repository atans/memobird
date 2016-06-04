<?php

namespace Atans\Memobird\Result;

class UserBindResult extends AbstractResult
{
    /**
     * @var int
     */
    protected $showapiUserid;

    /**
     * Get showapi_userid
     *
     * @return int
     */
    public function getShowapiUserid()
    {
        return $this->showapiUserid;
    }

    /**
     * Set showapi_userid
     *
     * @param  int $showapiUserid
     * @return $this
     */
    public function setShowapiUserid($showapiUserid)
    {
        $this->showapiUserid = $showapiUserid;
        return $this;
    }
}