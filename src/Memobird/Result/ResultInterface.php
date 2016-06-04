<?php

namespace Atans\Memobird\Result;

interface ResultInterface
{
    /**
     * Is success ?
     *
     * @return bool
     */
    public function success();

    /**
     * Get showapiResCode
     *
     * @return int
     */
    public function getShowapiResCode();

    /**
     * Set showapiResCode
     *
     * @param  int $showapiResCode
     * @return $this
     */
    public function setShowapiResCode($showapiResCode);

    /**
     * Get showapiResError
     *
     * @return string
     */
    public function getShowapiResError();

    /**
     * Set showapiResError
     *
     * @param  string $showapiResError
     * @return $this
     */
    public function setShowapiResError($showapiResError);
}