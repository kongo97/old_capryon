<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BinanceService;
use Illuminate\Support\Facades\Http;

class BinanceServiceTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */

    // ./vendor/bin/phpunit --filter testGetAllCrypto tests/Unit/BinanceServiceTest.php
    public function testGetAllCrypto()
    {
        $all = BinanceService::getAllCrypto();

        $this->assertTrue($all);
    }

    // ./vendor/bin/phpunit --filter testGetPrice tests/Unit/BinanceServiceTest.php
    public function testGetPrice()
    {
        $price = BinanceService::getPrice("ATAUSDT");

        print_r($price);

        $this->assertNotNull($price);
    }

    // ./vendor/bin/phpunit --filter testGetPriceChange tests/Unit/BinanceServiceTest.php
    public function testGetPriceChange()
    {
        $price = BinanceService::getPriceChange("ATAUSDT");

        print_r($price);

        $this->assertNotNull($price);
    }

    // ./vendor/bin/phpunit --filter testAnalyse tests/Unit/BinanceServiceTest.php
    public function testAnalyse()
    {
        $price = BinanceService::analyse();

        $this->assertTrue($price);
    }

    // ./vendor/bin/phpunit --filter testGetGoog tests/Unit/BinanceServiceTest.php
    public function testGetGoog()
    {
        $price = BinanceService::getGood(5);

        print_r($price);

        $this->assertNotNull($price);
    }
}
