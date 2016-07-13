<?php

namespace Atans\Memobird;

use Atans\Memobird\Content\PrintContentInterface;
use Doctrine\Common\Cache\Cache;
use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Memobird
{
    /**
     * @var string
     */
    protected $apiUrl = 'http://open.memobird.cn/';

    /**
     * @var string
     */
    protected $ak;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var
     */
    protected $logger;

    /**
     * @var string
     */
    protected $cacheKeyPrefix = '';

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Memobird constructor.
     *
     * @param string $ak
     * @param Cache $cache
     * @param bool $debug
     * @param null|Logger $logger
     */
    public function __construct($ak = null, Cache $cache = null, $debug = false, Logger $logger = null)
    {
        $this->setAk($ak);
        $this->setCache($cache);
        $this->setDebug($debug);
        $this->setLogger($logger);
    }

    /**
     * Print paper
     *
     * @param string $memobirdId
     * @param PrintContentInterface $printContent
     * @param int|null $userId
     * @return Result\PrintPaperResult
     */
    public function printPaper($memobirdId, PrintContentInterface $printContent, $userId = null)
    {
        if (is_null($userId)) {
            $userId = $this->getUserIdByMemobirdId($memobirdId);
        }

        $formParams = [
            'ak'           => $this->getAk(),
            'memobirdID'   => $memobirdId,
            'timestamp'    => $this->getTimestamp(),
            'printcontent' => (string) $printContent,
            'userID'       => $userId,
        ];

        $uri = 'home/printpaper';

        if ($this->getDebug()) {
            $this->getLogger()->addInfo(sprintf(
                'POST %s: %s',
                $this->apiUrl . $uri,
                http_build_query($formParams)
            ));
        }

        $response = $this->getClient()->request('POST', $uri, [
            'form_params' => $formParams,
        ]);

        if ($this->getDebug()) {
            $this->getLogger()->addInfo(sprintf('Response %s:%s', $response->getStatusCode(), $response->getBody()));
        }

        return $this->getSerializer()->deserialize($response->getBody(), Result\PrintPaperResult::class, 'json');
    }

    /**
     * Get print status
     *
     * @param int $printContentId
     * @return Result\PrintStatusResult
     */
    public function getPrintStatus($printContentId)
    {
        $query = [
            'ak'             => $this->getAk(),
            'timestamp'      => $this->getTimestamp(),
            'printcontentid' => $printContentId,
        ];

        $uri = 'home/getprintstatus';

        if ($this->getDebug()) {
            $this->getLogger()->addInfo(sprintf('GET %s: %s', $this->apiUrl . $uri, http_build_query($query)));
        }

        $response = $this->getClient()->request('GET', $uri, [
            'query' => $query,
        ]);

        if ($this->getDebug()) {
            $this->getLogger()->addInfo(sprintf('Response %s: %s', $response->getStatusCode(), $response->getBody()));
        }

        return $this->getSerializer()->deserialize($response->getBody(), Result\PrintStatusResult::class, 'json');
    }

    /**
     * Get user id by memobird id
     *
     * @param string $memobirdId
     * @return string
     * @throws Exception\UserIdNotFoundException
     */
    public function getUserIdByMemobirdId($memobirdId)
    {
        $key = $this->getCacheKey($memobirdId);

        $userId = $this->getCache()->fetch($key);
        if ($userId === false) {
            $response = $this->userBind($memobirdId);

            if (! $response->success()) {
                $message = sprintf(
                    "%s: Count not get User id by Memobird drive ID '%s'",
                    __METHOD__,
                    $memobirdId
                );

                if ($this->getDebug()) {
                    $this->getLogger()->error($message);
                }

                throw new Exception\UserIdNotFoundException($message);
            }

            $userId = $response->getShowapiUserid();

            $this->getCache()->save($key, $userId);
        }

        return $userId;
    }

    /**
     * UserBind
     *
     * @param $memobirdID
     * @param null $entifying
     * @return Result\UserBindResult
     */
    public function userBind($memobirdID, $entifying = null)
    {
        if (is_null($entifying)) {
            $entifying = $memobirdID;
        }

        $query = [
            'ak'         => $this->getAk(),
            'timestamp'  => $this->getTimestamp(),
            'memobirdID' => $memobirdID,
            'entifying'  => $entifying,
        ];

        $uri = 'home/setuserbind';

        if ($this->getDebug()) {
            $this->getLogger()->addInfo(sprintf('GET %s', $this->apiUrl . $uri), $query);
        }

        $response = $this->getClient()->request('GET', $uri, [
            'query' => $query,
        ]);

        if ($this->getDebug()) {
            $this->getLogger()->addInfo(sprintf('Response %s: %s', $response->getStatusCode(), $response->getBody()));
        }

        return $this->getSerializer()->deserialize($response->getBody(), Result\UserBindResult::class, 'json');
    }

    /**
     * Get ak
     *
     * @return string
     */
    public function getAk()
    {
        return $this->ak;
    }

    /**
     * Set ak
     *
     * @param  string $ak
     * @return $this
     */
    public function setAk($ak)
    {
        $this->ak = $ak;
        return $this;
    }

    /**
     * Get debug
     *
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Set debug
     *
     * @param  boolean $debug
     * @return $this
     */
    public function setDebug($debug)
    {
        $this->debug = (bool) $debug;
        return $this;
    }

    /**
     * Get cache key
     *
     * @param string $memobirdId
     * @return string
     */
    public function getCacheKey($memobirdId)
    {
        return $this->cacheKeyPrefix . $memobirdId;
    }

    /**
     * Get client
     *
     * @return Client
     */
    protected function getClient()
    {
        return new Client(['base_uri' => $this->apiUrl]);
    }

    /**
     * Get timestamp
     *
     * @return string
     */
    protected function getTimestamp()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get cache
     *
     * @return Cache
     */
    public function getCache()
    {
        if (! $this->cache instanceof  Cache) {
            $this->setCache(new \Doctrine\Common\Cache\FilesystemCache(__DIR__ . '/../../cache'));
        }
        return $this->cache;
    }

    /**
     * Set cache
     *
     * @param  Cache $cache
     * @return $this
     */
    public function setCache(Cache $cache = null)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Get logger
     *
     * @return Logger
     */
    public function getLogger()
    {
        if (! $this->logger instanceof Logger) {
            $this->setLogger((new Logger('memobird'))->pushHandler(new StreamHandler(__DIR__ . '/../.././memobird.log', Logger::INFO)));
        }

        return $this->logger;
    }

    /**
     * Set logger
     *
     * @param  Logger $logger
     * @return $this
     */
    public function setLogger(Logger $logger = null)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Get cacheKeyPrefix
     *
     * @return string
     */
    public function getCacheKeyPrefix()
    {
        return $this->cacheKeyPrefix;
    }

    /**
     * Set cacheKeyPrefix
     *
     * @param  string $cacheKeyPrefix
     * @return $this
     */
    public function setCacheKeyPrefix($cacheKeyPrefix)
    {
        $this->cacheKeyPrefix = $cacheKeyPrefix;
        return $this;
    }

    /**
     * Get serializer
     *
     * @return Serializer
     */
    public function getSerializer()
    {
        if (! $this->serializer instanceof  Serializer) {
            $this->setSerializer(new Serializer(array(new ObjectNormalizer()), array(new JsonEncoder())));
        }
        return $this->serializer;
    }

    /**
     * Set serializer
     *
     * @param  Serializer $serializer
     * @return $this
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }
}
