<?php
/**
 * File Paypal.php.
 *
 * @author Andrey Klimenko <andrey.iemail@gmail.com>
 * @see https://github.com/paypal/rest-api-sdk-php/blob/master/sample/
 * @see https://developer.paypal.com/webapps/developer/applications/accounts
 */

namespace ak;

define('PP_CONFIG_PATH', __DIR__);

use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use yii\base\Component;

use PayPal\Api\Details;
use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

/**
 * Class Paypal.
 *
 * @package ak
 * @author Andrey Klimenko <andrey.iemail@gmail.com>
 */
class Paypal extends Component
{
    //region Mode (production/development)
    const MODE_SANDBOX = 'sandbox';
    const MODE_LIVE    = 'live';
    //endregion

    //region Log levels
    /*
         * Logging level can be one of FINE, INFO, WARN or ERROR.
         * Logging is most verbose in the 'FINE' level and decreases as you proceed towards ERROR.
         */
    const LOG_LEVEL_FINE  = 'FINE';
    const LOG_LEVEL_INFO  = 'INFO';
    const LOG_LEVEL_WARN  = 'WARN';
    const LOG_LEVEL_ERROR = 'ERROR';
    //endregion

    //region API settings
    public $clientId;
    public $clientSecret;
    public $isProduction = false;
    public $currency = 'USD';

    public $_version = '3.0';

    public $config = [];
    //endregion

    /** @var ApiContext */
    private $_apiContext = null;

    protected $errors = [];

    public function init()
    {
        $this->setConfig();

        $credentials = new OAuthTokenCredential($this->clientId, $this->clientSecret);
        $credentials->getAccessToken($this->config);
        $this->_apiContext = new ApiContext($credentials);
    }

    public function payDemo()
    {
        $addr = new Address();
        $addr->setLine1('52 N Main ST');
        $addr->setCity('Johnstown');
        $addr->setCountryCode('US');
        $addr->setPostalCode('43210');
        $addr->setState('OH');

        $card = new CreditCard();
        $card->setNumber('4417119669820331');
        $card->setType('visa');
        $card->setExpireMonth('11');
        $card->setExpireYear('2018');
        $card->setCvv2('874');
        $card->setFirstName('Joe');
        $card->setLastName('Shopper');
        $card->setBillingAddress($addr);

        $fi = new FundingInstrument();
        $fi->setCreditCard($card);

        $payer = new Payer();
        $payer->setPaymentMethod('credit_card');
        $payer->setFundingInstruments(array($fi));

        $amountDetails = new Details();
        $amountDetails->setSubtotal('15.99');
        $amountDetails->setTax('0.03');
        $amountDetails->setShipping('0.03');

        $amount = new Amount();
        $amount->setCurrency('USD');
        $amount->setTotal('7.47');
        $amount->setDetails($amountDetails);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('This is the payment transaction description.');

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions(array($transaction));

        return $payment->create($this->_apiContext);
    }

    //region Private methods

    /**
     * Set configuration of the paypal system.
     *
     * @throws \yii\base\ErrorException
     */
    private function setConfig()
    {
        // Default config settings
        $config = [
            'http.ConnectionTimeOut' => 30,
            'http.Retry'             => 1,
            'mode'                   => self::MODE_SANDBOX, // development (sandbox) or production (live) mode
            'log.LogEnabled'         => YII_DEBUG ? 1 : 0,
            'log.FileName'           => \Yii::getAlias('@runtime/logs/paypal.log'),
            'log.LogLevel'           => self::LOG_LEVEL_FINE,
        ];

        // Set file name of the log if present
        if (isset($this->config['log.FileName'])
            && isset($this->config['log.LogEnabled'])
            && ((bool)$this->config['log.LogEnabled'] == true)
        ) {
            $logFileName = \Yii::getAlias($this->config['log.FileName']);

            if ($logFileName) {
                if (!file_exists($logFileName)) {
                    if (!touch($logFileName)) {
                        throw new ErrorException('Can\'t create paypal.log file at: ' . $logFileName);
                    }
                }
            }

            $this->config['log.FileName'] = $logFileName;
        }

        $this->config = ArrayHelper::merge($config, $this->config);
    }

    //endregion
}