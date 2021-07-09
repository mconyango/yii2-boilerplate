<?php
/**
 * Created by PhpStorm.
 * @author: Fred <mconyango@gmail.com>
 * Date: 2018-12-03 22:26
 * Time: 22:26
 */

namespace console\models\fakers\models;


use backend\modules\core\models\Country;
use backend\modules\core\models\Organization;
use console\models\fakers\FakerInterface;
use Faker\Factory;

class OrganizationFaker extends Organization implements FakerInterface
{
    use FakerModelTrait;

    public $countryIds;

    /**
     * @inheritdoc
     */
    public function getFakerInsertRow($rowNumber)
    {
        $faker = Factory::create();
        $date = $faker->dateTimeBetween('-1 months')->format('Y-m-d');
        return [
            'name' => $faker->company,
            'reg_no' => $faker->randomNumber(),
            'reg_date' => $date,
            'country' => $this->getCountryId(),
            'tax_pin' => $faker->randomNumber(),
            'business_class' => rand(Organization::BUSINESS_CLASS_SACCO, Organization::BUSINESS_CLASS_MFI),
            'notes' => $faker->text,
            'postal_address' => $faker->address,
            'phone' => '+' . rand(254700000000, 254799999999),
            'email' => $faker->email,
            'website' => $faker->url,
            'head_office_location' => $faker->streetAddress,
            'contact_person' => $faker->name,
        ];
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