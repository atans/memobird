<?php

namespace Atans\Memobird\Result;

abstract class AbstractResult implements ResultInterface
{
    const SHOW_API_RES_CODE_SUCCESS = 1;

    /**
     * @var int
     */
    protected $showapiResCode;

    /**
     * @var string
     */
    protected $showapiResError;

    /**
     * Is success ?
     *
     * @return bool
     */
    public function success()
    {
        return $this->getShowapiResCode() == self::SHOW_API_RES_CODE_SUCCESS;
    }

    /**
     * Get showapiResCode
     *
     * @return int
     */
    public function getShowapiResCode()
    {
        return $this->showapiResCode;
    }

    /**
     * Set showapiResCode
     *
     * @param  int $showapiResCode
     * @return $this
     */
    public function setShowapiResCode($showapiResCode)
    {
        $this->showapiResCode = $showapiResCode;
        return $this;
    }

    /**
     * Get showapiResError
     *
     * @return string
     */
    public function getShowapiResError()
    {
        return $this->showapiResError;
    }

    /**
     * Set showapiResError
     *
     * @param  string $showapiResError
     * @return $this
     */
    public function setShowapiResError($showapiResError)
    {
        $this->showapiResError = $showapiResError;
        return $this;
    }
}