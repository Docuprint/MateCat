<?php

namespace Url;

use Features\ReviewExtended\ReviewUtils;

class JobUrlStruct {

    const LABEL_T  = 't';
    const LABEL_R1 = 'r1';
    const LABEL_R2 = 'r2';

    /**
     * @var int
     */
    private $jid;

    /**
     * @var string
     */
    private $projectName;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * @var ?string
     */
    private $segmentId;

    /**
     * @var array
     */
    private $passwords = [];

    /**
     * @var array
     */
    private $urls = [];

    /**
     * JobUrlStruct constructor.
     *
     * @param int    $jid
     * @param string $projectName
     * @param string $source
     * @param string $target
     * @param array  $passwords
     * @param null   $httpHost
     * @param null   $segmentId
     *
     * @throws \Exception
     */
    public function __construct( $jid, $projectName, $source, $target, array $passwords = [], $httpHost = null, $segmentId = null ) {
        $this->jid         = $jid;
        $this->projectName = $projectName;
        $this->source      = $source;
        $this->target      = $target;
        $this->passwords   = $passwords;
        $this->segmentId   = $segmentId;
        $this->setUrls($httpHost);
    }

    /**
     * @param $label
     *
     * @return bool
     */
    private function isLabelAllowed( $label ) {
        $allowed = [ self::LABEL_T, self::LABEL_R1, self::LABEL_R2 ];

        return in_array( $label, $allowed );
    }

    /**
     * set $this->urls from provided password
     *
     * @param null $httpHost
     *
     * @throws \Exception
     */
    private function setUrls($httpHost = null) {
        // loop passwords array
        foreach ( $this->passwords as $label => $password ) {
            if ( $this->isLabelAllowed( $label ) ) {

                switch ( $label ) {
                    default:
                    case self::LABEL_T:
                        $revisionNumber = null;
                        break;
                    case self::LABEL_R1:
                        $revisionNumber = 1;
                        break;
                    case self::LABEL_R2:
                        $revisionNumber = 2;
                        break;
                }

                $sourcePage = ReviewUtils::revisionNumberToSourcePage( $revisionNumber );

                $url = $this->httpHost($httpHost);
                $url .= DIRECTORY_SEPARATOR;
                $url .= $this->getJobType( $sourcePage );
                $url .= DIRECTORY_SEPARATOR;
                $url .= $this->projectName;
                $url .= DIRECTORY_SEPARATOR;
                $url .= $this->source . '-' . $this->target;
                $url .= DIRECTORY_SEPARATOR;
                $url .= $this->jid . '-' . $password;

                if ( $this->segmentId ) {
                    $url .= '#' . $this->segmentId;
                }

                $this->passwords[ $label ] = $url;
            }
        }
    }

    /**
     * @param null $httpHost
     *
     * @return mixed
     * @throws \Exception
     */
    private function httpHost( $httpHost = null ) {
        $host = \INIT::$HTTPHOST;

        if ( !empty( $httpHost ) ) {
            $host = $httpHost;
        }

        if ( empty( $host ) ) {
            throw new \Exception( 'HTTP_HOST is not set ' );
        }

        return $host;
    }

    /**
     * Get the job type:
     *
     * - translate
     * - revise
     * - revise(n)
     *
     * @param $sourcePage
     *
     * @return string|null
     */
    private function getJobType( $sourcePage ) {
        if ( $sourcePage == 1 ) {
            return 'translate';
        }

        if ( $sourcePage == 2 ) {
            return 'revise';
        }

        if ( $sourcePage > 2 ) {
            return 'revise' . ( $sourcePage - 1 );
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getTranslateUrl() {
        return $this->urls[ self::LABEL_T ];
    }

    /**
     * @return mixed
     */
    public function getReviseUrl() {
        return $this->urls[ self::LABEL_R1 ];
    }

    /**
     * @return mixed
     */
    public function getRevise2Url() {
        return $this->urls[ self::LABEL_R2 ];
    }

    /**
     * @return array
     */
    public function getUrls() {
        return $this->urls;
    }

    /**
     * @return bool
     */
    public function hasReview() {
        return isset( $this->urls[ self::LABEL_R1 ] ) and !isset( $this->urls[ self::LABEL_R2 ] );
    }

    /**
     * @return bool
     */
    public function hasSecondPassReview() {
        return isset( $this->urls[ self::LABEL_R2 ] );
    }
}