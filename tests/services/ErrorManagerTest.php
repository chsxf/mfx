<?php

use chsxf\MFX\Config;
use chsxf\MFX\ConfigManager;
use chsxf\MFX\ErrorManager;
use chsxf\MFX\Services\ISessionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DummySessionService implements ISessionService
{
    public function setInSession(array $values)
    {
    }

    public function unsetInSession(string ...$keys)
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}

final class ErrorManagerTest extends TestCase
{
    private static ?ErrorManager $errorManager = null;

    public static function setUpBeforeClass(): void
    {
        $configManager = new ConfigManager();
        $configManager->load(new Config([]));
        $dummySessionManager = new DummySessionService();
        self::$errorManager = new ErrorManager($configManager, $dummySessionManager);
    }

    public static function tearDownAfterClass(): void
    {
        self::$errorManager = null;
    }

    public function tearDown(): void
    {
        self::$errorManager->flush();
    }

    #[Test]
    public function testError(): void
    {
        $this->assertFalse(self::$errorManager->hasError());
        trigger_error('test_error');
        $this->assertTrue(self::$errorManager->hasError());
    }

    #[Test]
    public function testNotif(): void
    {
        $this->assertFalse(self::$errorManager->hasNotif());
        trigger_notif('test_notif');
        $this->assertTrue(self::$errorManager->hasNotif());
    }

    #[Test]
    public function testNotifs(): void
    {
        $this->assertFalse(self::$errorManager->hasNotif());
        trigger_notifs(['test_notif1', 'test_notif2', 'test_notif3']);
        $this->assertTrue(self::$errorManager->hasNotif());
    }

    #[Test]
    public function testFlushToArray(): void
    {
        trigger_error('test_error');
        trigger_notifs(['test_notif1', 'test_notif2', 'test_notif3']);

        $arr = [];
        self::$errorManager->flushToArrayOrObject($arr);

        $this->assertArrayHasKey('errors', $arr);
        $this->assertCount(1, $arr['errors']);

        $this->assertArrayHasKey('notifs', $arr);
        $this->assertCount(3, $arr['notifs']);
    }

    #[Test]
    public function testFlushToObject(): void
    {
        trigger_error('test_error');
        trigger_notifs(['test_notif1', 'test_notif2', 'test_notif3']);

        $obj = (object) null;
        self::$errorManager->flushToArrayOrObject($obj);

        $this->assertObjectHasProperty('errors', $obj);
        $this->assertCount(1, $obj->errors);

        $this->assertObjectHasProperty('notifs', $obj);
        $this->assertCount(3, $obj->notifs);
    }
}
