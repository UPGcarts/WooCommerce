<?php

namespace Upg\Library\Tests\Request\Objects;

use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Amount;
use Upg\Library\Risk\RiskClass;
use Upg\Library\Basket\BasketItemType;
use Upg\Library\Tests\Request\AbstractRequestTest;
use Upg\Library\Validation\Validation;
use Faker\Factory as Factory;

class BasketItemTest extends AbstractRequestTest
{

    /**
     * @var string A very long string
     */
    private $veryLongString;

    /**
     * @var Generator
     */
    private $faker;

    public function setUp()
    {
        $faker = Factory::create();

        $this->veryLongString = preg_replace("/[^A-Za-z0-9]/", '', $faker->sentence(90));
        $this->faker = $faker;
    }

    public function tearDown()
    {
        unset($this->faker);
    }

    private function getBasketItemAmount()
    {
        $amount = new Amount();
        $amount->setAmount(9200)->setVatAmount(1840)->setVatRate(20);

        return $amount;
    }

    public function testBasketItemValidationSuccess()
    {
        $basketItem = new BasketItem();
        $basketItem->setBasketItemText($this->faker->name)
            ->setBasketItemID('1')
            ->setBasketItemCount(1)
            ->setBasketItemAmount($this->getBasketItemAmount())
            ->setBasketItemRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setBasketItemType(BasketItemType::BASKET_ITEM_TYPE_DEFAULT);

        $validation = new Validation();
        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        $this->assertEmpty($data);
    }

    public function testBasketItemValidationBasketItemText()
    {
        $basketItem = new BasketItem();
        $basketItem->setBasketItemID('1')
            ->setBasketItemCount(1)
            ->setBasketItemAmount($this->getBasketItemAmount())
            ->setBasketItemRiskClass(RiskClass::RISK_CLASS_DEFAULT);

        $validation = new Validation();
        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        /**
         * Test required validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemText',
            'basketItemText is required',
            $data,
            "basketItemText is required did not trigger"
        );

        $basketItem->setBasketItemText($this->faker->sentence(255));

        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        /**
         * Test the length validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemText',
            'basketItemText must be no more than 500 characters',
            $data,
            "basketItemText must be no more than 500 characters did not trigger"
        );
    }

    public function testBasketItemValidationBasketItemID()
    {
        $validation = new Validation();

        $basketItem = new BasketItem();
        $basketItem->setBasketItemText($this->faker->name)
            ->setBasketItemID('1234567890123456789012345')
            ->setBasketItemCount(1)
            ->setBasketItemAmount($this->getBasketItemAmount())
            ->setBasketItemRiskClass(RiskClass::RISK_CLASS_DEFAULT);

        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        /**
         * Test length validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemID',
            'basketItemID must be no more than 20 characters and alphanumeric',
            $data,
            "basketItemID must be no more than 20 characters and alphanumeric did not trigger"
        );

        /**
         * Test non alphanumeric
         */
        $basketItem->setBasketItemID('A-b');
        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemID',
            'basketItemID must be no more than 20 characters and alphanumeric',
            $data,
            "basketItemID must be no more than 20 characters and alphanumeric did not trigger"
        );
    }

    public function testBasketItemValidationBasketItemCount()
    {
        $validation = new Validation();

        $basketItem = new BasketItem();
        $basketItem->setBasketItemText($this->faker->name)
            ->setBasketItemID('1')
            ->setBasketItemAmount($this->getBasketItemAmount())
            ->setBasketItemRiskClass(RiskClass::RISK_CLASS_DEFAULT);

        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        /**
         * Test required
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemCount',
            'basketItemCount is required',
            $data,
            "basketItemCount is required did not trigger"
        );

        $basketItem->setBasketItemCount(12345678);
        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemCount',
            'basketItemCount must be an integer and no more than 5 digits',
            $data,
            "basketItemCount must be an integer and no more than 5 digits did not trigger"
        );
    }

    public function testBasketItemValidationBasketItemAmount()
    {
        $basketItem = new BasketItem();
        $basketItem->setBasketItemText($this->faker->name)
            ->setBasketItemID('1')
            ->setBasketItemCount(1)
            ->setBasketItemRiskClass(RiskClass::RISK_CLASS_DEFAULT);

        $validation = new Validation();
        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemAmount',
            'basketItemAmount is required',
            $data,
            "basketItemAmount is required did not trigger"
        );
    }

    public function testBasketItemValidationRiskClass()
    {
        $basketItem = new BasketItem();
        $basketItem->setBasketItemText($this->faker->name)
            ->setBasketItemID('1')
            ->setBasketItemAmount($this->getBasketItemAmount())
            ->setBasketItemCount(1)
            ->setBasketItemRiskClass('FOO');

        $test = get_class($this);

        $validation = new Validation();
        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemRiskClass',
            'basketItemRiskClass must certain values or be empty',
            $data,
            "basketItemRiskClass must certain values or be empty did not trigger"
        );
    }

    public function testBasketItemValidationBasketItemType()
    {
        $basketItem = new BasketItem();
        $basketItem->setBasketItemText($this->faker->name)
            ->setBasketItemID('1')
            ->setBasketItemCount(1)
            ->setBasketItemAmount($this->getBasketItemAmount())
            ->setBasketItemRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setBasketItemType('foo');

        $validation = new Validation();
        $validation->getValidator($basketItem);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\BasketItem',
            'basketItemType',
            'basketItemType must certain values or be empty',
            $data,
            "basketItemType must certain values or be empty did not trigger"
        );
    }
}
