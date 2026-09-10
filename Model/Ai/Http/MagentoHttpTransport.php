<?php
declare(strict_types=1);

namespace Vera\SearchAI\Model\Ai\Http;

use JsonException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;
use Throwable;
use Vera\SearchAI\Model\Ai\Exception\ProviderConfigurationException;
use Vera\SearchAI\Model\Ai\Exception\ProviderException;
use Vera\SearchAI\Model\Ai\Exception\ProviderRateLimitedException;

class MagentoHttpTransport implements HttpTransportInterface
{
    public function __construct(
        private readonly CurlFactory $curlFactory,
        private readonly LoggerInterface $logger
    ) {}

    public function post(string $url, array $headers, array $payload, int $timeout, int $maxBytes): array
    {
        $curl = $this->curlFactory->create();
        $curl->setHeaders(array_merge(['Content-Type' => 'application/json'], $headers));
        $curl->setTimeout($timeout);
        $curl->setOption(CURLOPT_FOLLOWLOCATION, false);
        try {
            $curl->post($url, json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
            $status = (int) $curl->getStatus();
            $body = (string) $curl->getBody();
        } catch (JsonException $exception) {
            throw new ProviderException('Unable to encode the AI request.', 0, $exception);
        } catch (Throwable $exception) {
            $this->logger->warning('SearchAI provider transport failure.', ['exception' => $exception::class]);
            throw new ProviderException('AI provider transport failed.', 0, $exception);
        }
        if ($status === 429) {
            throw new ProviderRateLimitedException('AI provider rate limit reached.');
        }
        if ($status === 401 || $status === 403) {
            throw new ProviderConfigurationException('AI provider authentication failed.');
        }
        if ($status < 200 || $status >= 300) {
            $this->logger->warning('SearchAI provider returned an HTTP error.', ['status' => $status]);
            throw new ProviderException('AI provider request failed.');
        }
        if (strlen($body) > $maxBytes) {
            throw new ProviderException('AI provider response exceeded the configured limit.');
        }
        try {
            $decoded = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ProviderException('AI provider returned malformed JSON.', 0, $exception);
        }
        if (!is_array($decoded)) {
            throw new ProviderException('AI provider returned an invalid response.');
        }
        return $decoded;
    }
}
