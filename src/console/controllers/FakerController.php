<?php
/**
 * Created by PhpStorm.
 * @author: Fred <fred@btimillman.com>
 * Date & Time: 2017-05-23 8:00 PM
 */

namespace console\controllers;


use backend\modules\auth\models\AuditTrail;
use backend\modules\auth\models\Users;
use backend\modules\conf\settings\EmailSettings;
use backend\modules\core\models\Bank;
use backend\modules\core\models\BankBranch;
use backend\modules\core\models\BranchArea;
use backend\modules\core\models\Country;
use backend\modules\core\models\Currency;
use backend\modules\core\models\DocumentType;
use backend\modules\core\models\EducationLevel;
use backend\modules\core\models\Employer;
use backend\modules\core\models\EmploymentTerm;
use backend\modules\core\models\Gender;
use backend\modules\core\models\IdentityType;
use backend\modules\core\models\MaritalStatus;
use backend\modules\core\models\Organization;
use backend\modules\core\models\OrganizationBranch;
use backend\modules\core\models\OrganizationDocument;
use backend\modules\core\models\PersonRelationshipType;
use backend\modules\core\models\PostalCode;
use backend\modules\core\models\Religion;
use backend\modules\core\models\Salutation;
use console\models\fakers\Faker;
use backend\modules\conf\models\NumberingFormat;
use backend\modules\core\models\Client;
use backend\modules\core\models\ClientBankAccount;
use backend\modules\core\models\ClientDocument;
use backend\modules\core\models\ClientKin;
use backend\modules\core\models\ClientKinTrustee;
use backend\modules\core\models\ClientKinTrusteeDocument;
use backend\modules\core\models\ClientResidence;
use backend\modules\core\models\ClientWorkInformation;
use Yii;
use yii\console\Controller;

class FakerController extends Controller
{
    public function actionSettings()
    {
        $settings = Yii::$app->settings;
        $this->stdout($settings->get(EmailSettings::SECTION_EMAIL, EmailSettings::KEY_HOST) . "\n");
    }

    public function actionRun()
    {
        $time_start = microtime(true);
        $this->loadFakeData();
        $time_end = microtime(true);
        $executionTime = round($time_end - $time_start, 2);
        $this->stdout("FAKER EXECUTED IN {$executionTime} SECONDS\n");
    }

    public function actionClear()
    {
        $this->canExecuteFaker();
        $this->clearFakeData();
    }

    protected function loadFakeData()
    {
        $this->canExecuteFaker();
        $this->clearFakeData();
        Faker::getInstance(new \console\models\fakers\models\OrganizationFaker(), 10, [])->run();
        Faker::getInstance(new \console\models\fakers\models\ClientFaker(), 100, [])->run();
    }

    protected function clearFakeData()
    {
        $sql = "SET FOREIGN_KEY_CHECKS=0;";
        $sql .= "TRUNCATE " . ClientDocument::tableName() . ";";
        $sql .= "TRUNCATE " . ClientKin::tableName() . ";";
        $sql .= "TRUNCATE " . ClientResidence::tableName() . ";";
        $sql .= "TRUNCATE " . ClientWorkInformation::tableName() . ";";
        $sql .= "TRUNCATE " . ClientBankAccount::tableName() . ";";
        $sql .= "TRUNCATE " . Client::tableName() . ";";
        $sql .= "TRUNCATE " . BranchArea::tableName() . ";";
        $sql .= "TRUNCATE " . OrganizationBranch::tableName() . ";";
        $sql .= "TRUNCATE " . OrganizationDocument::tableName() . ";";
        $sql .= "TRUNCATE " . Employer::tableName() . ";";
        $sql .= "DELETE FROM " . BankBranch::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . Bank::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . EmploymentTerm::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . Religion::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . PersonRelationshipType::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . Salutation::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . DocumentType::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . Gender::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . Country::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . IdentityType::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . PostalCode::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . MaritalStatus::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . EducationLevel::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . Currency::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . Users::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "DELETE FROM " . AuditTrail::tableName() . " WHERE [[org_id]] IS NOT NULL;";
        $sql .= "TRUNCATE " . Organization::tableName() . ";";
        $sql .= "UPDATE " . NumberingFormat::tableName() . " SET [[next_number]]=1 WHERE [[id]]=:client_number_format;";
        $sql .= "UPDATE " . NumberingFormat::tableName() . " SET [[next_number]]=1 WHERE [[id]]=:org_number_format;";
        $sql .= "SET FOREIGN_KEY_CHECKS=1;";
        Yii::$app->db->createCommand($sql, [
            ':client_number_format' => Client::NUMBERING_FORMAT_ID,
            ':org_number_format' => Organization::NUMBERING_FORMAT_ID,
        ])->execute();

        //reset auto increment values
        $sql = "ALTER TABLE " . BankBranch::tableName() . " AUTO_INCREMENT = :bank_branch_next_id;";
        $sql .= "ALTER TABLE " . Bank::tableName() . " AUTO_INCREMENT = :bank_next_id;";
        $sql .= "ALTER TABLE " . EmploymentTerm::tableName() . " AUTO_INCREMENT = :employment_term_next_id;";
        $sql .= "ALTER TABLE " . Religion::tableName() . " AUTO_INCREMENT = :religion_next_id;";
        $sql .= "ALTER TABLE " . PersonRelationshipType::tableName() . " AUTO_INCREMENT = :person_relationship_type_next_id;";
        $sql .= "ALTER TABLE " . Salutation::tableName() . " AUTO_INCREMENT = :salutation_next_id;";
        $sql .= "ALTER TABLE " . DocumentType::tableName() . " AUTO_INCREMENT = :document_type_next_id;";
        $sql .= "ALTER TABLE " . Gender::tableName() . " AUTO_INCREMENT = :gender_next_id;";
        $sql .= "ALTER TABLE " . Country::tableName() . " AUTO_INCREMENT = :country_next_id;";
        $sql .= "ALTER TABLE " . IdentityType::tableName() . " AUTO_INCREMENT = :identity_type_next_id;";
        $sql .= "ALTER TABLE " . PostalCode::tableName() . " AUTO_INCREMENT = :postal_code_next_id;";
        $sql .= "ALTER TABLE " . MaritalStatus::tableName() . " AUTO_INCREMENT = :marital_status_next_id;";
        $sql .= "ALTER TABLE " . EducationLevel::tableName() . " AUTO_INCREMENT = :education_level_next_id;";
        $sql .= "ALTER TABLE " . Currency::tableName() . " AUTO_INCREMENT = :currency_next_id;";
        $sql .= "ALTER TABLE " . Users::tableName() . " AUTO_INCREMENT = :users_next_id;";
        $sql .= "ALTER TABLE " . AuditTrail::tableName() . " AUTO_INCREMENT = :audit_trail_next_id;";
        Yii::$app->db->createCommand($sql, [
            ':bank_branch_next_id' => (int)BankBranch::getScalar('max([[id]])') + 1,
            ':bank_next_id' => (int)Bank::getScalar('max([[id]])') + 1,
            ':employment_term_next_id' => (int)EmploymentTerm::getScalar('max([[id]])') + 1,
            ':religion_next_id' => (int)Religion::getScalar('max([[id]])') + 1,
            ':person_relationship_type_next_id' => (int)PersonRelationshipType::getScalar('max([[id]])') + 1,
            ':salutation_next_id' => (int)Salutation::getScalar('max([[id]])') + 1,
            ':document_type_next_id' => (int)DocumentType::getScalar('max([[id]])') + 1,
            ':gender_next_id' => (int)Gender::getScalar('max([[id]])') + 1,
            ':country_next_id' => (int)Country::getScalar('max([[id]])') + 1,
            ':identity_type_next_id' => (int)IdentityType::getScalar('max([[id]])') + 1,
            ':postal_code_next_id' => (int)PostalCode::getScalar('max([[id]])') + 1,
            ':marital_status_next_id' => (int)MaritalStatus::getScalar('max([[id]])') + 1,
            ':education_level_next_id' => (int)EducationLevel::getScalar('max([[id]])') + 1,
            ':currency_next_id' => (int)Currency::getScalar('max([[id]])') + 1,
            ':users_next_id' => (int)Users::getScalar('max([[id]])') + 1,
            ':audit_trail_next_id' => (int)AuditTrail::getScalar('max([[id]])') + 1,
        ])->execute();

    }

    protected function canExecuteFaker()
    {
        if (YII_ENV === 'prod') {
            $this->stdout("FAKER CANNOT BE EXECUTED\n");
            Yii::$app->end();
        }
    }
}