<?php
/**
 * File Paypal.php.
 *
 * @author Andrey Klimenko <andrey.iemail@gmail.com>
 */

namespace ak;

use yii\base\Component;

/**
 * Class Paypal.
 *
 * @package ak
 * @author Andrey Klimenko <andrey.iemail@gmail.com>
 */
class Paypal extends Component
{
    //region API settings
    public $user;
    public $password;
    public $signature;
    public $isProduction = false;
    public $currency = 'USD';

    private $version = '3.0';
    //endregion

    protected $errors = [];
} 