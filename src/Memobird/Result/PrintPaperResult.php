<?php

namespace Atans\Memobird\Result;

class PrintPaperResult extends AbstractResult
{
    /**
     * @var int
     */
    protected $result;

    /**
     * @var string
     */
    protected $smartGuid;

    /**
     * @var int
     */
    protected $printcontentid;

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @inheritdoc
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSmartGuid()
    {
        return $this->smartGuid;
    }

    /**
     * @inheritdoc
     */
    public function setSmartGuid($smartGuid)
    {
        $this->smartGuid = $smartGuid;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrintcontentid()
    {
        return $this->printcontentid;
    }

    /**
     * @inheritdoc
     */
    public function setPrintcontentid($printcontentid)
    {
        $this->printcontentid = $printcontentid;
        return $this;
    }
}
