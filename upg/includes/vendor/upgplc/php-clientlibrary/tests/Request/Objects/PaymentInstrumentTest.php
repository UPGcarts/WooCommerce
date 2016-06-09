<?php
namespace Upg\Library\Tests\Request\Objects;

use Upg\Library\Request\Objects\PaymentInstrument;
use Faker\Factory as Factory;
use Faker\Provider\Payment as Payment;
use Upg\Library\Tests\Request\AbstractRequestTest;
use Upg\Library\Validation\Validation;

class PaymentInstrumentTest extends AbstractRequestTest
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

    public function testPaymentInstrumentTestValidationSuccess()
    {
        /**
         * Test card instrument success
         */
        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD)
            ->setAccountHolder($this->faker->name)
            ->setNumber($this->faker->creditCardNumber)
            ->setIssuer(PaymentInstrument::ISSUER_MC)
            ->setValidity(new \DateTime('now'));

        $validation = new Validation();
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");

        /**
         * Test bank account
         */
        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_BANK)
            ->setAccountHolder($this->faker->name)
            ->setIban('FI1350001540000056')
            ->setBic('OKOYFIHH');

        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testPaymentInstrumentTestValidationPaymentInstrumentType()
    {
        $validation = new Validation();
        $payment = new PaymentInstrument();
        $payment->setAccountHolder($this->faker->name)
            ->setNumber($this->faker->creditCardNumber)
            ->setIssuer(PaymentInstrument::ISSUER_MC)
            ->setValidity(new \DateTime('now'));

        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'paymentInstrumentType',
            'PaymentInstrumentType is required',
            $data,
            "PaymentInstrumentType is required validation failed"
        );

        $payment->setPaymentInstrumentType($this->faker->name);
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'paymentInstrumentType',
            'PaymentInstrumentType must be certain values',
            $data,
            "PaymentInstrumentType must be certain values validation failed"
        );
    }

    public function testPaymentInstrumentTestValidationAccountHolder()
    {
        $validation = new Validation();

        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD)
            ->setNumber($this->faker->creditCardNumber)
            ->setIssuer(PaymentInstrument::ISSUER_MC)
            ->setValidity(new \DateTime('now'));

        /**
         * Validate required
         */
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'accountHolder',
            'AccountHolder is required',
            $data,
            "AccountHolder is required validation failed"
        );

        $payment->setAccountHolder($this->veryLongString);
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        /**
         * Validate Length
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'accountHolder',
            'AccountHolder must be less than or equal to 50 characters',
            $data,
            "AccountHolder must be less than or equal to 50 characters validation failed"
        );

    }

    public function testPaymentInstrumentTestValidationNumber()
    {
        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD)
            ->setAccountHolder($this->faker->name)
            ->setNumber($this->veryLongString)
            ->setIssuer(PaymentInstrument::ISSUER_MC)
            ->setValidity(new \DateTime('now'));

        $validation = new Validation();
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        /**
         * Length validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'number',
            'Number must be less than or equal to 16 characters',
            $data,
            "Number must be less than or equal to 16 characters validation failed"
        );

        $payment->setNumber('');
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'number',
            'For card payments number must be set',
            $data,
            "For card payments number must be set validation failed"
        );

    }

    public function testPaymentInstrumentTestValidationValidity()
    {
        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD)
            ->setAccountHolder($this->faker->name)
            ->setNumber($this->faker->creditCardNumber)
            ->setIssuer(PaymentInstrument::ISSUER_MC);

        $validation = new Validation();
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        /**
         * Test required when card payment
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'validity',
            'For card payments validity must be set',
            $data,
            "For card payments number must be set validation failed"
        );

        /**
         * Test the required is not triggered when payment is bank
         * The way we are doing this is to trigger validation error on a bank transfer
         * but assert that the number validation has not triggered
         */
        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_BANK)
            ->setAccountHolder($this->faker->name)
            ->setBic('OKOYFIHH');

        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertNotEmpty($data, "The IBAN test should of trigered and it has not");
        $this->assertValidationHasNotReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'validity',
            'For card payments validity must be set',
            $data,
            "For card payments number must be set validation failed"
        );

    }

    public function testPaymentInstrumentTestValidationIssuer()
    {
        $validation = new Validation();

        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD)
            ->setAccountHolder($this->faker->name)
            ->setNumber($this->faker->creditCardNumber)
            ->setIssuer($this->faker->name)
            ->setValidity(new \DateTime('now'));

        /**
         * Validate issuer to be set to certain values
         */
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'issuer',
            'PaymentInstrumentType must be certain values',
            $data,
            "PaymentInstrumentType must be certain values validation failed"
        );

        $payment->setIssuer('');
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'issuer',
            'For card payments issuer must be set',
            $data,
            "For card payments issuer must be set validation failed"
        );
    }

    public function testPaymentInstrumentTestValidationIban()
    {

        $validation = new Validation();

        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_BANK)
            ->setAccountHolder($this->faker->name)
            ->setIban($this->veryLongString)
            ->setBic('OKOYFIHH');

        /**
         * Test length
         */
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'iban',
            'Iban must be no more than 34 characters',
            $data,
            "Iban must be no more than 34 characters validation failed"
        );

        /**
         * Test required
         */
        $payment->setIban("");
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'iban',
            'For bank payments iban must be set',
            $data,
            "For bank payments iban must be set validation failed"
        );
    }

    public function testPaymentInstrumentTestValidationBic()
    {
        $validation = new Validation();

        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_BANK)
            ->setAccountHolder($this->faker->name)
            ->setIban("FI1350001540000056")
            ->setBic('');

        /**
         * test required
         */
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'bic',
            'For bank payments bic must be set',
            $data,
            "For bank payments bic must be set validation failed"
        );

        /**
         * Format test
         */
        $payment->setBic($this->veryLongString);
        $validation->getValidator($payment);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\PaymentInstrument',
            'bic',
            'Bic must be 11 characters long and contain alphanumeric characters',
            $data,
            "Bic must be 11 characters long and contain alphanumeric characters validation failed"
        );


    }
}
