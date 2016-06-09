<?php

namespace Upg\Library\Tests\Request\Objects;

use Upg\Library\Request\Objects\Address as Address;
use Upg\Library\Tests\Request\AbstractRequestTest;
use Upg\Library\Validation\Validation;
use Faker\Factory as Factory;

class AddressTest extends AbstractRequestTest
{
    private $veryLongString;

    public function setUp()
    {
        $faker = Factory::create();

        $this->veryLongString = preg_replace("/[^A-Za-z0-9]/", '', $faker->sentence(90));
    }

    /**
     * Validate successful processing
     */
    public function testAddressTestValidationSuccess()
    {
        $address = new Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setZip("LS12 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        $validation = new Validation();
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    /**
     * Run validation checks on street
     */
    public function testAddressTestValidationStreetFailure()
    {
        $address = new Address();
        $address->setNo(45)
            ->setZip("LS12 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        $validation = new Validation();
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'street',
            'Street is required',
            $data,
            "Street required validation failed"
        );

        $address->setStreet("LoremipsumdolorsitametconsecteturadipiscingelitPraesentsitametdictumnequequiseuismodarcu");
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'street',
            'Street must be between 1 and 80 characters',
            $data,
            "Street length validation did not work"
        );

    }

    /**
     * Test validation on the no field
     */
    public function testAddressTestValidationNoFailure()
    {
        $validation = new Validation();

        $address = new Address();
        $address->setNo(null)
            ->setStreet("Test")
            ->setZip("LS12 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        $validation->getValidator($address);
        $data = $validation->performValidation();

        /**
         * Test the house no required
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'no',
            'House number is required',
            $data,
            "House number required check failed"
        );

        /** Length tests */
        $address->setNo("LoremipsumdolorsitametconsecteturadipiscingelitPraesentsitametdictumnequequiseuismodarcu");
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'no',
            'House number must be between 1 and 32 characters',
            $data,
            "House number length check failed"
        );
    }

    /**
     * Test the zip validation
     */
    public function testAddressTestValidationZipFailure()
    {
        $validation = new Validation();

        $address = new Address();
        $address->setNo(45)
            ->setStreet("Test")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        /**
         * test required validation
         */
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'zip',
            'ZIP/Postal Code is required',
            $data,
            "ZIP/Postal Code is required check failed"
        );

        /**
         * Test length validation
         */
        $address->setZip("12345678901234567890");
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'zip',
            'ZIP/Postal must be between 1 and 16 characters',
            $data,
            "ZIP/Postal must be between 1 and 16 characterscheck failed"
        );
    }

    /**
     * Test City validation
     */
    public function testAddressTestValidationCityFailure()
    {
        $validation = new Validation();

        $address = new Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setZip("LS12 4TN")
            ->setState("State")
            ->setCountry("GB");

        /**
         * Test Required validation
         */
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'city',
            'City is required',
            $data,
            "City is required check failed"
        );

        /**
         * Test length validation
         */
        $address->setCity($this->veryLongString);
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'city',
            "City must be between 1 and 80 characters",
            $data,
            "City must be between 1 and 80 characters check failed"
        );
    }

    public function testAddressTestValidationCountryFailure()
    {
        $validation = new Validation();

        $address = new Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setCity("foo")
            ->setZip("LS12 4TN")
            ->setState("State");

        /**
         * Test Required validation
         */
        $validation->getValidator($address);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'country',
            'Country is required',
            $data,
            "Country is required validation failed"
        );

        /**
         * Test the country code validation which must be alphabetical 2 characters
         */
        $address->setCountry('DEU');
        $validation->getValidator($address);
        $data = $validation->performValidation();
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'country',
            'Country must be an 2 letter ISO 3166 code',
            $data,
            "Country must be an 2 letter ISO 3166 code failed"
        );

        $address->setCountry('D');
        $data = $validation->performValidation();
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'country',
            'Country must be an 2 letter ISO 3166 code',
            $data,
            "Country must be an 2 letter ISO 3166 code failed"
        );
    }

    public function testAddressTestValidationStateFailure()
    {
        $validation = new Validation();

        $address = new Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setCity("foo")
            ->setZip("LS12 4TN")
            ->setCountry("GB");

        /**
         * Test Length validation
         */
        $address->setState($this->veryLongString);

        $validation->getValidator($address);
        $data = $validation->performValidation();
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Address',
            'state',
            'State must between 1 and 80 characters',
            $data,
            "State must between 1 and 80 characters failed"
        );

    }
}
