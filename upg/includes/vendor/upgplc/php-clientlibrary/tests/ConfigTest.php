<?php

namespace Upg\Library\Tests;

use Faker\Factory as Factory;
use Upg\Library\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator
     */
    private $faker;

    public function setUp()
    {
        $faker = Factory::create();
        $this->faker = $faker;
    }

    public function tearDown()
    {
        unset($this->faker);
    }

    public function testSetData()
    {
        /**
         *  ['merchantID'] string This is the merchantID assigned by PayCo.
         *  ['storeID'] string This is the store ID of a merchant.
         *  ['logEnabled'] string Should logging be enabled
         *  ['logLevel'] int Log level
         *  ['logLocationMain'] string Main log Location
         *  ['logLocationRequest'] string Log location for API requests
         *  ['logLocationMNS'] string Log for MNS asynchronous callbacks
         *  ['logLocationCallbacks'] string Log location for synchronous callbacks
         *  ['defaultRiskClass'] string Default risk class
         */
        $config = array(
            'merchantID' => $this->faker->randomNumber(8),
            'storeID' => $this->faker->word,
            'logEnabled' => $this->faker->boolean,
            'logLevel' => Config::LOG_LEVEL_ERROR,
            'logLocationMain' => $this->faker->word,
            'logLocationRequest' => $this->faker->word,
            'logLocationMNS' => $this->faker->word,
            'logLocationCallbacks' => $this->faker->word,
            'defaultRiskClass' => 2,
            'defaultLocale' => strtoupper($this->faker->languageCode),
        );

        $configObject = new Config();
        $configObject->setData($config);

        $this->assertEquals($config['merchantID'], $configObject->getMerchantID(), "Merchant ID was set incorrectly");
        $this->assertEquals($config['storeID'], $configObject->getStoreID(), "Store ID was set incorrectly");
        $this->assertEquals($config['logEnabled'], $configObject->getLogEnabled(), "Log Enabled was set incorrectly");
        $this->assertEquals($config['logLevel'], $configObject->getLogLevel(), "Log Level was set incorrectly");
        $this->assertEquals(
            $config['logLocationMain'],
            $configObject->getLogLocationMain(),
            "Main Log location was set incorrectly"
        );
        $this->assertEquals(
            $config['logLocationRequest'],
            $configObject->getLogLocationRequest(),
            "Request Log location was set incorrectly"
        );
        $this->assertEquals(
            $config['logLocationMNS'],
            $configObject->getLogLocationMNS(),
            "MNS Log location was set incorrectly"
        );
        $this->assertEquals(
            $config['logLocationCallbacks'],
            $configObject->getLogLocationCallbacks(),
            "Callback Log location was set incorrectly"
        );
        $this->assertEquals(
            $config['defaultRiskClass'],
            $configObject->getDefaultRiskClass(),
            "Default Risk was set incorrectly"
        );
        $this->assertEquals(
            $config['defaultLocale'],
            $configObject->getDefaultLocale(),
            "Default was set incorrectly"
        );

    }
}