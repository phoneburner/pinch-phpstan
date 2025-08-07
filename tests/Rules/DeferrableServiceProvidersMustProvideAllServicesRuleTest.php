<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Phpstan\Tests\Rules;

use PhoneBurner\Pinch\Component\MessageBus\Handler\InvokableMessageHandler;
use PhoneBurner\Pinch\Component\MessageBus\MessageBus;
use PhoneBurner\Pinch\Framework\MessageBus\TransportFactory\RedisTransportFactory;
use PhoneBurner\Pinch\Phpstan\Collectors\ServiceProviderClassCollector;
use PhoneBurner\Pinch\Phpstan\Collectors\ServiceProviderRegistrationsCollector;
use PhoneBurner\Pinch\Phpstan\Rules\DeferrableServiceProvidersMustProvideAllServicesRule as SUT;
use PhoneBurner\Pinch\Phpstan\Tests\Rules\Fixtures\DeferrableServiceProvidersMustProvideAllServicesRule\BarServiceProvider;
use PhoneBurner\Pinch\Phpstan\Tests\Rules\Fixtures\DeferrableServiceProvidersMustProvideAllServicesRule\BazServiceProvider;
use PhoneBurner\Pinch\Phpstan\Tests\Rules\Fixtures\DeferrableServiceProvidersMustProvideAllServicesRule\QuxServiceProvider;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<SUT>
 */
final class DeferrableServiceProvidersMustProvideAllServicesRuleTest extends RuleTestCase
{
    private const string FIXTURE_BASE_DIR = __DIR__ . '/Fixtures/DeferrableServiceProvidersMustProvideAllServicesRule/';

    protected function getRule(): SUT
    {
        return new SUT(self::createReflectionProvider());
    }

    #[\Override]
    protected function getCollectors(): array
    {
        return [
            new ServiceProviderClassCollector(),
            new ServiceProviderRegistrationsCollector(),
        ];
    }

    public function testRule(): void
    {
        $this->analyse([
            self::FIXTURE_BASE_DIR . 'FooServiceProvider.php',
            self::FIXTURE_BASE_DIR . 'BarServiceProvider.php',
            self::FIXTURE_BASE_DIR . 'BazServiceProvider.php',
            self::FIXTURE_BASE_DIR . 'QuxServiceProvider.php',
        ], [
            [\sprintf(SUT::NOT_IN_PROVIDES, BarServiceProvider::class, MessageBus::class), 25],
            [\sprintf(SUT::NOT_IN_PROVIDES, BazServiceProvider::class, RedisTransportFactory::class), 25],
            [\sprintf(SUT::NOT_IN_BIND_OR_REGISTER, QuxServiceProvider::class, InvokableMessageHandler::class), 26],
        ]);
    }

    #[\Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../config/extension.neon'];
    }
}
