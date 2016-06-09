<?php

namespace Upg\Library\Tests\Request\Objects;

use Upg\Library\Request\Objects\Address;
use Upg\Library\Request\Objects\CompanyMember as CompanyMember;
use Upg\Library\Request\Objects\Person;
use Upg\Library\Tests\Request\AbstractRequestTest;
use Upg\Library\Validation\Validation;
use Faker\Factory as Factory;

class CompanyMemberTest extends AbstractRequestTest
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

    private function getPerson()
    {
        $person = new Person();
        $person->setSalutation(Person::SALUTATIONMALE)
            ->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setDateOfBirth(new \DateTime())
            ->setEmail($this->faker->email)
            ->setPhoneNumber('555666')
            ->setFaxNumber('555454');

        return $person;
    }

    private function getAddress()
    {
        $address = new Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setZip("LS12 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        return $address;
    }

    public function testCompanyTestValidationCompanyMemberSuccess()
    {
        $companyMember = new CompanyMember();

        $companyMember->setContactData($this->getPerson())
            ->setNationality('UK')
            ->setResidence($this->getAddress());

        $validation = new Validation();
        $validation->getValidator($companyMember);
        $data = $validation->performValidation();

        $this->assertEmpty($data);
    }

    public function testCompanyTestValidationCompanyMemberContactDataValidation()
    {
        $companyMember = new CompanyMember();

        $companyMember->setNationality('UK')
            ->setResidence($this->getAddress());

        $validation = new Validation();
        $validation->getValidator($companyMember);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\CompanyMember',
            'contactData',
            'ContactData is required',
            $data,
            "ContactData is required did not trigger"
        );
    }

    public function testCompanyTestValidationCompanyMemberNationalityValidation()
    {
        $companyMember = new CompanyMember();

        $companyMember->setContactData($this->getPerson())
            ->setResidence($this->getAddress());

        /**
         * Required validation
         */
        $validation = new Validation();
        $validation->getValidator($companyMember);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\CompanyMember',
            'nationality',
            'Nationality is required',
            $data,
            "Nationality is required failed to trigger"
        );

        /**
         * Format validation
         */
        $companyMember->setNationality("FOO");
        $validation->getValidator($companyMember);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\CompanyMember',
            'nationality',
            'Nationality must be alphanumeric and two characters',
            $data,
            "Nationality must be alphanumeric and two character validation failed to trigger"
        );
    }

    public function testCompanyTestValidationCompanyMemberResidenceValidation()
    {
        $companyMember = new CompanyMember();

        $companyMember->setContactData($this->getPerson())
            ->setNationality('UK');

        $validation = new Validation();
        $validation->getValidator($companyMember);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\CompanyMember',
            'residence',
            'Residence is required',
            $data,
            "Residence is required validation failed to trigger"
        );
    }
}
