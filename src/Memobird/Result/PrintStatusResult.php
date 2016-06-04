<?php

namespace Atans\Memobird\Result;

class PrintStatusResult extends AbstractResult
{
    /**
     * @var int
     */
    protected $printflag;

    /**
     * @var string
     */
    protected $printcontentid;

    /**
     * Get printflag
     *
     * @return int
     */
    public function getPrintflag()
    {
        return $this->printflag;
    }

    /**
     * Set printflag
     *
     * @param  int $printflag
     * @return $this
     */
    public function setPrintflag($printflag)
    {
        $this->printflag = $printflag;
        return $this;
    }

    /**
     * Get printcontentid
     *
     * @return string
     */
    public function getPrintcontentid()
    {
        return $this->printcontentid;
    }

    /**
     * Set printcontentid
     *
     * @param  string $printcontentid
     * @return $this
     */
    public function setPrintcontentid($printcontentid)
    {
        $this->printcontentid = $printcontentid;
        return $this;
    }
}