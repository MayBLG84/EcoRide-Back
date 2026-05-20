<?php

namespace App\Service\Security\Limiter;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Psr\Container\ContainerInterface;

class RateLimitService
{
    public function __construct(
        #[AutowireLocator([
            'signup_request.limiter' => RateLimiterFactory::class,
            'signup_block.limiter' => RateLimiterFactory::class,

            'login_request.limiter' => RateLimiterFactory::class,
            'login_block.limiter' => RateLimiterFactory::class,

            'contact_request.limiter' => RateLimiterFactory::class,
            'contact_block.limiter' => RateLimiterFactory::class,

            'search_request.limiter' => RateLimiterFactory::class,
            'search_block.limiter' => RateLimiterFactory::class,
        ])]
        private readonly ContainerInterface $limiters,
    ) {}

    public function consumeLimiter(string $type, string $key): bool
    {
        /** @var RateLimiterFactory $requestLimiter */
        $requestLimiter = $this->limiters->get($type . '_request.limiter');

        /** @var RateLimiterFactory $blockLimiter */
        $blockLimiter = $this->limiters->get($type . '_block.limiter');

        $block = $blockLimiter->create($key)->consume();

        if (!$block->isAccepted()) {
            return false;
        }

        $result = $requestLimiter->create($key)->consume();

        if (!$result->isAccepted()) {
            $blockLimiter->create($key)->consume(1);

            return false;
        }

        return true;
    }

    public function resetLimiter(string $type, string $key): void
    {
        $this->limiters->get($type . '_request.limiter')
            ->create($key)
            ->reset();

        $this->limiters->get($type . '_block.limiter')
            ->create($key)
            ->reset();
    }
}
