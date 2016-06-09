<?php
namespace Upg\Library\Tests\Request\Objects;

use Upg\Library\Locale\Codes;
use Upg\Library\Request\Objects\HostedPagesText;
use Faker\Factory as Factory;
use Faker\Provider\Payment as Payment;
use Upg\Library\Tests\Request\AbstractRequestTest;
use Upg\Library\Validation\Validation;

class HostedPagesTextTest extends AbstractRequestTest
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

    public function testHostedPagesTextValidationSuccess()
    {
        /**
         * Test Hosted Pages Text success
         */
        $hostedPagesText = new HostedPagesText();
        $hostedPagesText->setPaymentMethodType(HostedPagesText::PAYMENT_METHOD_TYPE_CC)
            ->setFee(500)
            ->setDescription("Some payment description")
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testPersonTestValidationPaymentMethodType()
    {
        $validation = new Validation();

        $hostedPagesText = new HostedPagesText();
        $hostedPagesText->setFee(500)
            ->setDescription("Some payment description")
            ->setLocale(Codes::LOCALE_EN);

        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'paymentMethodType',
            'paymentMethodType is required',
            $data,
            "paymentMethodType is required did not trigger"
        );

        $hostedPagesText->setPaymentMethodType($this->faker->name);
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'paymentMethodType',
            'paymentMethodType must be certain values',
            $data,
            "paymentMethodType must be certain values did not trigger"
        );
    }

    public function testPersonTestValidationFee()
    {
        $hostedPagesText = new HostedPagesText();
        $hostedPagesText->setPaymentMethodType(HostedPagesText::PAYMENT_METHOD_TYPE_CC)
            ->setFee(500.001)
            ->setDescription("Some payment description")
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'fee',
            'Fee must be a numeric non decimal place value and no more than 16 characters',
            $data,
            "Fee must be a numeric non decimal place value and no more than 16 characters did not trigger"
        );

        $hostedPagesText->setFee(500000000000000000);
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'fee',
            'Fee must be a numeric non decimal place value and no more than 16 characters',
            $data,
            "Fee must be a numeric non decimal place value and no more than 16 characters did not trigger when length"
        );

    }

    public function testPersonTestValidationDescription()
    {
        $hostedPagesText = new HostedPagesText();
        $hostedPagesText->setPaymentMethodType(HostedPagesText::PAYMENT_METHOD_TYPE_CC)
            ->setFee(500)
            ->setDescription('')
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'description',
            'Description is required',
            $data,
            "Description is required did not trigger"
        );

        $hostedPagesText->setDescription($this->faker->sentence(90));
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'description',
            'Description must be no more than 255 characters long',
            $data,
            "Description must be no more than 255 characters long did not trigger"
        );
    }

    public function testPersonTestValidationLocale()
    {
        $hostedPagesText = new HostedPagesText();
        $hostedPagesText->setPaymentMethodType(HostedPagesText::PAYMENT_METHOD_TYPE_CC)
            ->setFee(500)
            ->setDescription("Some payment description")
            ->setLocale('');

        $validation = new Validation();
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'locale',
            'Locale is required',
            $data,
            "Locale is required did not trigger"
        );

        $hostedPagesText->setLocale($this->faker->name);
        $validation->getValidator($hostedPagesText);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\HostedPagesText',
            'locale',
            'Locale must be certain values',
            $data,
            "Locale must be certain values did not trigger"
        );
    }
}
