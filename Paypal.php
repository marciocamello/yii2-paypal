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

use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\CreditCard;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use yii\helpers\VarDumper;

use yii\base\Component;
use PayPal\Auth\OAuthTokenCredential;

function D($object, $exit = false)
{
    VarDumper::dump($object, 20, 1);
    echo '<br />';

    if ($exit) {
        exit();
    }

    return null;
}

/**
 * Class Paypal.
 *
 * @package ak
 * @author Andrey Klimenko <andrey.iemail@gmail.com>
 */
class Paypal extends Component
{
    //region API settings
    public $clientId;
    public $clientSecret;
    public $isProduction = false;
    public $currency = 'USD';

    private $version = '3.0';
    //endregion

    private $_token = null;
    /** @var ApiContext */
    private $_apiContext = null;

    protected $errors = [];

    public function initDemo()
    {
        $this->clientId     = 'AbtvThBiEwaAysJbhOyI6VST02vs1mLCdJv8F8oCmZJUZNzLwQeHLuZiOF7r';
        $this->clientSecret = 'ENM9BhCEllRx5CpmZdfb0dOnM4FAwGR42XXfYqKEQhv4KhuuJyeXFBeN2gQz';
    }

    public function authorize()
    {
        $credentials = new OAuthTokenCredential($this->clientId, $this->clientSecret);
        if (is_null($this->_token)) {
            $credentials->getAccessToken(['mode' => 'sandbox']);
        }
        $this->_apiContext = new ApiContext($credentials);
    }

    public function payDemo()
    {
        $this->authorize();

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

        $amountDetails = new \PayPal\Api\Details();
        $amountDetails->setSubtotal('7.41');
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

        $payment->create($this->_apiContext);
    }
} 