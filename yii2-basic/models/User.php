<?php

namespace app\models;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'alexy',
            'password' => 'qw34rt',
            'authKey' => '100keyForReportAndSms',
            'accessToken' => '100-tokenForReportAndSms',
        ],
        '101' => [
            'id' => '101',
            'username' => 'konstantin',
            'password' => 'as34df',
            'authKey' => '101keyForReportAndSms',
            'accessToken' => '101-tokenForReportAndSms',
        ],
        '102' => [
            'id' => '102',
            'username' => 'anna',
            'password' => 'rt56ui',
            'authKey' => '102keyForReportAndSms',
            'accessToken' => '102-tokenForReportAndSms',
        ],
        '103' => [
            'id' => '103',
            'username' => 'artem',
            'password' => 'zx67cv',
            'authKey' => '103keyForReportAndSms',
            'accessToken' => '103-tokenForReportAndSms',
        ],
        '104' => [
            'id' => '104',
            'username' => 'mihail',
            'password' => 'gh76jk',
            'authKey' => '104keyForReportAndSms',
            'accessToken' => '104-tokenForReportAndSms',
        ],
        '105' => [
            'id' => '105',
            'username' => 'ilya',
            'password' => 'yg67rd',
            'authKey' => '105keyForReportAndSms',
            'accessToken' => '105-tokenForReportAndSms',
        ],
    ];


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
