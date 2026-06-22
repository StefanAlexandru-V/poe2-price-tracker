<?php

namespace Tests\Unit;

use App\Models\Alert;
use PHPUnit\Framework\TestCase;

class AlertTest extends TestCase
{
    public function test_less_than_triggers_when_price_below_threshold(): void
    {
        $alert = new Alert(['operator' => '<', 'threshold' => 10.0]);
        $this->assertTrue($alert->isTriggered(9.5));
        $this->assertFalse($alert->isTriggered(10.0));
        $this->assertFalse($alert->isTriggered(10.5));
    }

    public function test_greater_than_triggers_when_price_above_threshold(): void
    {
        $alert = new Alert(['operator' => '>', 'threshold' => 5.0]);
        $this->assertTrue($alert->isTriggered(5.1));
        $this->assertFalse($alert->isTriggered(5.0));
        $this->assertFalse($alert->isTriggered(4.9));
    }

    public function test_less_than_or_equal_triggers_at_boundary(): void
    {
        $alert = new Alert(['operator' => '<=', 'threshold' => 1.5]);
        $this->assertTrue($alert->isTriggered(1.5));
        $this->assertTrue($alert->isTriggered(1.0));
        $this->assertFalse($alert->isTriggered(1.6));
    }

    public function test_greater_than_or_equal_triggers_at_boundary(): void
    {
        $alert = new Alert(['operator' => '>=', 'threshold' => 100.0]);
        $this->assertTrue($alert->isTriggered(100.0));
        $this->assertTrue($alert->isTriggered(200.0));
        $this->assertFalse($alert->isTriggered(99.9));
    }
}
