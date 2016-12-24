<?php

namespace LiquidWeb\SslCertificate\Exceptions;

use Throwable;
use LiquidWeb\SslCertificate\Url;
use function LiquidWeb\SslCertificate\str_contains as str_contains;

class Handler
{
    protected $thrown;

    public function __construct(Throwable $thrown)
    {
        $this->thrown = $thrown;
    }

    public function downloadHandler(Url $parsedUrl)
    {
        $errorMsg = $this->thrown->getMessage();
        if (str_contains($errorMsg, 'getaddrinfo failed')) {
            throw CouldNotDownloadCertificate::hostDoesNotExist($parsedUrl->getHostName());
        }

        if (str_contains($errorMsg, 'error:14090086')) {
            throw CouldNotDownloadCertificate::noCertificateInstalled($parsedUrl->getHostName());
        }

        if (str_contains($errorMsg, 'error:14077410') || str_contains($errorMsg, 'error:140770FC')) {
            throw CouldNotDownloadCertificate::failedHandshake($parsedUrl);
        }

        if (str_contains($errorMsg, '(Connection timed out)')) {
            throw CouldNotDownloadCertificate::connectionTimeout($parsedUrl->getTestURL());
        }

        throw CouldNotDownloadCertificate::unknownError($parsedUrl->getTestURL(), $errorMsg);
    }
}
