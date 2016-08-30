<?php

namespace Atans\Memobird\Content;

interface PrintContentInterface
{
    const CONTENT_MAX_WIDTH = 384;

    /**
     * P:{photo}
     * T:{text}
     */
    const CONTENT_DELIMITER = ':';

    /**
     * {photo}|{text}|{photo}
     */
    const CONTENTS_DELIMITER = '|';

    const TYPE_TEXT  = 'T';
    const TYPE_PHOTO = 'P';

    /**
     * Convert to gbk
     *
     * @param string $string
     * @return string
     */
    public function convert($string);

    /**
     * Encode string
     *
     * @param string $type
     * @param string $content
     * @return string
     */
    public function encode($type, $content);

    /**
     * Get print content
     *
     * @return string
     */
    public function getPrintContent();
}