<?php

namespace Upg\Library\Tests\Request\Objects;

use Upg\Library\Request\Objects\Company as Company;
use Upg\Library\Tests\Request\AbstractRequestTest;
use Upg\Library\Validation\Validation;
use Faker\Factory as Factory;

class CompanyTest extends AbstractRequestTest
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

    public function testCompanyTestValidationSuccess()
    {
        $company = new Company();

        $company->setCompanyName($this->faker->name)
            ->setCompanyRegistrationID("111")
            ->setCompanyVatID("111")
            ->setCompanyTaxID("111");

        $validation = new Validation();
        $validation->getValidator($company);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testCompanyTestValidationCompanyNameFailure()
    {
        $validation = new Validation();

        $company = new Company();

        $company->setCompanyName($this->veryLongString)
            ->setCompanyRegistrationID("111")
            ->setCompanyVatID("111")
            ->setCompanyTaxID("111");

        $validation->getValidator($company);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Company',
            'companyName',
            'CompanyName must be between 1 and 100 characters',
            $data,
            "Company Length validation did not trigger"
        );
    }

    public function testCompanyTestValidationCompanyRegistrationIDFailure()
    {
        $validation = new Validation();

        $company = new Company();

        $company->setCompanyName($this->faker->name)
            ->setCompanyRegistrationID($this->veryLongString)
            ->setCompanyVatID("111")
            ->setCompanyTaxID("111");

        $validation->getValidator($company);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Company',
            'companyRegistrationID',
            'CompanyRegistrationID must be between 1 and 100 characters',
            $data,
            "CompanyRegistrationID length validation did not trigger"
        );
    }

    public function testCompanyTestValidationCompanyVatIDFailure()
    {
        $company = new Company();

        $company->setCompanyName($this->faker->name)
            ->setCompanyRegistrationID("111")
            ->setCompanyVatID($this->veryLongString)
            ->setCompanyTaxID("111");

        $validation = new Validation();
        $validation->getValidator($company);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Company',
            'companyVatID',
            'CompanyVatID must be between 1 and 100 characters',
            $data,
            "CompanyVatID length validation did not trigger"
        );
    }

    public function testCompanyTestValidationCompanyTaxIDFailure()
    {
        $company = new Company();

        $company->setCompanyName($this->faker->name)
            ->setCompanyRegistrationID("111")
            ->setCompanyVatID("111")
            ->setCompanyTaxID($this->veryLongString);

        $validation = new Validation();
        $validation->getValidator($company);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Company',
            'companyTaxID',
            'CompanyTaxID must be between 1 and 100 characters',
            $data,
            "CompanyTaxID length validation did not trigger"
        );
    }

    public function testCompanyTestValidationCompanyRegisterType()
    {
        $company = new Company();

        /**
         * Test successful validation
         */
        $company->setCompanyName($this->faker->name)
            ->setCompanyRegistrationID("111")
            ->setCompanyVatID("222")
            ->setCompanyTaxID("333")
            ->setCompanyRegisterType(Company::COMPANY_TYPE_FN);

        $validation = new Validation();
        $validation->getValidator($company);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");

        /**
         * Test unsuccessful company validation
         */
        $company->setCompanyRegisterType($this->faker->name);
        $validation->getValidator($company);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Objects\\Company',
            'companyRegisterType',
            'CompanyRegisterType must certain values or be empty',
            $data,
            "CompanyRegisterType must certain values or be empty validation did not trigger"
        );
    }
}
