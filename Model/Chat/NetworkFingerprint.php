<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Chat;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class NetworkFingerprint
{
    public function __construct(
        private readonly RemoteAddress $remoteAddress,
        private readonly DeploymentConfig $deploymentConfig
    ) {
    }

    public function create(): string
    {
        $address = trim((string) $this->remoteAddress->getRemoteAddress());
        $secret = (string) $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);
        if ($secret === '') {
            $secret = 'vera-searchai-local-fingerprint';
        }
        return hash_hmac('sha256', $address === '' ? 'unknown' : $address, $secret);
    }
}
