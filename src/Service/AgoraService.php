<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AgoraService
{
    private string $appId = '49da528bddc74e3089d232ca596b856e';
    private string $appCertificate = 'b2fea15c8e104b378f0541866c194344';

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function generateToken(string $channelName, int $uid, int $expireTimeInSeconds = 3600): string
    {
        $currentTimestamp = time();
        $privilegeExpireTs = $currentTimestamp + $expireTimeInSeconds;

        $command = sprintf(
            'node %s/js/generateToken.js %s %s %s %d %d',
            escapeshellarg(__DIR__ . '/../../scripts'),
            escapeshellarg($this->appId),
            escapeshellarg($this->appCertificate),
            escapeshellarg($channelName),
            $uid,
            $privilegeExpireTs
        );

        $token = shell_exec($command);

        if ($token === null) {
            throw new HttpException(500, 'Failed to generate token.');
        }

        return trim($token);
    }

}