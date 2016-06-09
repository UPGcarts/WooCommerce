<?php

namespace Upg\Library\Tests\Request\Objects;

use Faker\Factory as Factory;
use Upg\Library\Request\Objects\Person as Person;
use Upg\Library\Tests\Request\AbstractRequestTest;
use Upg\Library\Validation\Validation;

class PersonTest extends AbstractRequestTest
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
        date_default_timezone_set('Europe/Berlin');

        $faker = Factory::create();

        $this->veryLongString = preg_replace("/[^A-Za-z0-9]/", '', $faker->sentence(90));
        $this->faker = $faker;
    }

    public function tearDown()
    {
        unset($this->faker);
    }

    /**
     * Test successful validation
     */
    public function testPersonTestValidationSuccess()
    {
        $person = new Person();
        $person->setSalutation(PERSON::SALUTATIONMALE)
            ->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setDateOfBirth(new \DateTime())
            ->setEmail($this->faker->email)
            ->setPhoneNumber('555666')
            ->setFaxNumber('555454');

        $validation = new Validation();
        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testPersonTestValidationSalutationFailure()
    {
        $validation = new Validation();

        /**
         * Test required
         */
        $person = new Person();
        $person->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setDateOfBirth(new \DateTime())
            ->setEmail($this->faker->email)
            ->setPhoneNumber('555666')
            ->setFaxNumber('555454');

        $validation = new Validation();
        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'salutation',
            'Salutation is required',
            $data,
            "Salutation is required validation failed"
        );

        /**
         * Test the call back
         */
        $person->setSalutation("a");
        $validation->getValidator($person);
        $data = $validation->performValidation();
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'salutation',
            'Salutation must be M or F',
            $data,
            "Salutation must be M or F validation failed"
        );

    }

    public function testPersonTestValidationNameFailure()
    {
        $validation = new Validation();

        /**
         * Test required
         */
        $person = new Person();
        $person->setSalutation(PERSON::SALUTATIONFEMALE)
            ->setSurname($this->faker->name)
            ->setDateOfBirth(new \DateTime())
            ->setEmail($this->faker->email)
            ->setPhoneNumber('555666')
            ->setFaxNumber('555454');

        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'name',
            'Name is required',
            $data,
            "Name is required validation failed"
        );

        /** Test length */
        $person->setName($this->veryLongString);
        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'name',
            'Name must be less than 50 characters',
            $data,
            "Name must be less than 50 characters validation failed"
        );

    }

    public function testPersonTestValidationSurnameFailure()
    {
        $validation = new Validation();

        /**
         * Test required
         */
        $person = new Person();
        $person->setSalutation(PERSON::SALUTATIONFEMALE)
            ->setName($this->faker->name)
            ->setDateOfBirth(new \DateTime())
            ->setEmail($this->faker->email)
            ->setPhoneNumber('555666')
            ->setFaxNumber('555454');

        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'surname',
            'Surname is required',
            $data,
            "Surname is required validation failed"
        );

        /** Test length */
        $person->setSurname($this->veryLongString);
        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'surname',
            'Surname must be less than 50 characters',
            $data,
            "Surname must be less than 50 characters validation failed"
        );
    }

    public function testPersonTestValidationEmailFailure()
    {
        $validation = new Validation();

        /**
         * Test required
         */
        $person = new Person();
        $person->setSalutation(PERSON::SALUTATIONFEMALE)
            ->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setDateOfBirth(new \DateTime())
            ->setPhoneNumber('555666')
            ->setFaxNumber('555454');

        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'email',
            'Email is required',
            $data,
            "Email is required validation failed"
        );

        /** Test length */
        $person->setEmail($this->veryLongString);
        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'email',
            'Email must be less than 50 characters',
            $data,
            "Email must be less than 50 characters validation failed"
        );
    }

    public function testPersonTestValidationPhoneNumberFailure()
    {
        $validation = new Validation();

        /**
         * Test length
         */
        $person = new Person();
        $person->setSalutation(PERSON::SALUTATIONFEMALE)
            ->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setEmail($this->faker->email)
            ->setDateOfBirth(new \DateTime())
            ->setPhoneNumber("1234567890123456789012345678901234567890111")
            ->setFaxNumber('555454');

        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'phoneNumber',
            'Phone Number must be less than 30 characters',
            $data,
            "Phone Number must be less than 30 characters validation failed"
        );
    }

    public function testPersonTestValidationFaxNumberFailure()
    {
        $validation = new Validation();

        /**
         * Test length
         */
        $person = new Person();
        $person->setSalutation(PERSON::SALUTATIONFEMALE)
            ->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setEmail($this->faker->email)
            ->setDateOfBirth(new \DateTime())
            ->setFaxNumber('1234567890123456789012345678901234567890111');

        $validation->getValidator($person);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Person',
            'faxNumber',
            'Fax Number must be less than 30 characters',
            $data,
            "Fax Number must be less than 30 characters validation failed"
        );
    }

    /**
     * As the phone number must be numeric the library will strip out any non numeric characters
     */
    public function testSetPhoneNumber()
    {
        $person = new Person();
        $person->setPhoneNumber($this->faker->phoneNumber);

        $this->assertNotEmpty($person->getPhoneNumber(), "No phone number was set");
        $this->assertRegExp('/^\d+$/', $person->getPhoneNumber(), "Phone number contains non numeric characters");

        /**
         * Test strip out mechanism works
         */
        $person->setPhoneNumber("555447B");
        $this->assertNotEmpty($person->getPhoneNumber(), "No phone number was set");
        $this->assertRegExp('/^\d+$/', $person->getPhoneNumber(), "Phone number contains non numeric characters");

    }

    /**
     * As the fax number must be numeric the library will strip out any non numeric characters
     */
    public function testSetFaxNumber()
    {
        $person = new Person();
        $person->setFaxNumber($this->faker->phoneNumber);

        $this->assertNotEmpty($person->getFaxNumber(), "No phone number was set");
        $this->assertRegExp('/^\d+$/', $person->getFaxNumber(), "Phone number contains non numeric characters");

        /**
         * Test strip out mechanism works
         */
        $person->setFaxNumber("555447B");
        $this->assertNotEmpty($person->getFaxNumber(), "No phone number was set");
        $this->assertRegExp('/^\d+$/', $person->getFaxNumber(), "Phone number contains non numeric characters");

    }
}
