<?php
/**
 * Created by PhpStorm.
 * @author Fred <mconyango@gmail.com>
 * Date: 2018-05-29
 * Time: 12:41
 */

namespace console\models\fakers\models;


use backend\modules\core\models\Organization;
use console\models\fakers\FakerInterface;
use backend\modules\core\models\Country;
use backend\modules\core\models\Client;
use backend\modules\core\models\Gender;
use backend\modules\core\models\IdentityType;
use backend\modules\core\models\OrganizationBranch;
use backend\modules\core\models\Salutation;
use Faker\Factory;

class ClientFaker extends Client implements FakerInterface
{
    use FakerModelTrait;

    public $orgIds;
    public $identityTypeIds;
    public $genderIds;
    public $salutationIds;
    public $countryIds;

    /**
     * @inheritdoc
     */
    public function getFakerInsertRow($rowNumber)
    {
        $faker = Factory::create();
        $date = $faker->dateTimeBetween('-1 months')->format('Y-m-d');
        $org_id = $this->getOrgId();
        $branch_id = OrganizationBranch::getScalar('id', ['org_id' => $org_id]);
        return [
            'org_id' => $org_id,
            'branch_id' => $branch_id,
            'salutation' => $this->getSalutationCode(),
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'identity_type_id' => $this->getIdentityTypeId(),
            'national_identity' => rand(25249759, 26249759),
            'gender_id' => $this->getGenderId(),
            'phone' => '+' . rand(254700000000, 254799999999),
            'date_joined' => $date,
            'tax_pin' => $faker->randomNumber(),
            'status' => Client::STATUS_ACTIVE,
            'account_type' => rand(Client::ACCOUNT_TYPE_BOSA, Client::ACCOUNT_TYPE_BOTH),
            'is_non_member' => $faker->boolean,
            'country' => $this->getCountryId(),
            'postal_address' => $faker->address,
            'postal_code' => $faker->postcode,
            'email' => $faker->email,
        ];
    }

    /**
     * @return array|mixed
     * @throws \yii\base\ExitException
     */
    public function getOrgId()
    {
        return $this->getFakerForeignKeyId('orgIds', Organization::class);
    }

    /**
     * @return array|mixed
     * @throws \yii\base\ExitException
     */
    public function getIdentityTypeId()
    {
        return $this->getFakerForeignKeyId('identityTypeIds', IdentityType::class);
    }

    /**
     * @return array|mixed
     * @throws \yii\base\ExitException
     */
    public function getGenderId()
    {
        return $this->getFakerForeignKeyId('genderIds', Gender::class);
    }

    /**
     * @return array|mixed
     * @throws \yii\base\ExitException
     */
    public function getSalutationCode()
    {
        return $this->getFakerForeignKeyId('salutationIds', Salutation::class, 'name');
    }

    /**
     * @return array|mixed
     * @throws \yii\base\ExitException
     */
    public function getCountryId()
    {
        return $this->getFakerForeignKeyId('countryIds', Country::class);
    }
}