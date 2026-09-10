<?php
declare(strict_types=1);

namespace Vera\SearchAI\Controller\Chat;

use JsonException;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException as FrameworkInvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Vera\SearchAI\Model\Chat\ChatService;
use Vera\SearchAI\Model\Chat\Exception\InvalidRequestException;
use Vera\SearchAI\Model\Chat\Exception\RateLimitExceededException;

class Send implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $jsonFactory,
        private readonly FormKeyValidator $formKeyValidator,
        private readonly StoreManagerInterface $storeManager,
        private readonly SessionManagerInterface $session,
        private readonly ChatService $chatService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(): Json
    {
        $result = $this->jsonFactory->create();

        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->error(
                $result,
                400,
                'invalid_form_key',
                (string) __('Invalid form key.')
            );
        }

        try {
            $historyJson = (string) $this->request->getParam('history', '[]');
            if (strlen($historyJson) > 20000) {
                throw new InvalidRequestException('History is too large.');
            }
            $history = json_decode($historyJson, true, 64, JSON_THROW_ON_ERROR);
            $response = $this->chatService->execute(
                (string) $this->request->getParam('message', ''),
                $history,
                (int) $this->storeManager->getStore()->getId(),
                (string) $this->session->getSessionId()
            );
            return $result->setData($response->toArray());
        } catch (JsonException | InvalidRequestException) {
            return $this->error($result, 400, 'invalid_request', (string) __('Please enter a valid message.'));
        } catch (RateLimitExceededException) {
            return $this->error(
                $result,
                429,
                'rate_limited',
                (string) __('Too many requests. Please wait a moment and try again.')
            );
        } catch (Throwable $exception) {
            $this->logger->warning('SearchAI chat request failed.', ['exception' => $exception::class]);
            return $this->error(
                $result,
                503,
                'temporarily_unavailable',
                (string) __('The assistant is temporarily unavailable. Please try again.')
            );
        }
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ?FrameworkInvalidRequestException
    {
        return null;
    }

    private function error(Json $result, int $status, string $code, string $message): Json
    {
        return $result->setHttpResponseCode($status)->setData([
            'success' => false,
            'answer' => $message,
            'products' => [],
            'error_code' => $code,
        ]);
    }
}
