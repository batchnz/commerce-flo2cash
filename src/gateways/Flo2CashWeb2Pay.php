<?php

namespace batchnz\flo2cash\gateways;

use Craft;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\Transaction;
use craft\commerce\omnipay\base\OffsiteGateway;
use craft\commerce\elements\Order;
use batchnz\flo2cash\events\SetTransactionParticular;
use batchnz\flo2cash\events\SetTransactionReference;
use Omnipay\Common\AbstractGateway;
use Omnipay\Flo2Cash\Flo2CashItemBag;
use Omnipay\Flo2Cash\Web2PayGateway as Gateway;

/**
 * Web2Pay represents the Hosted Payment Pages
 *
 * @author    Josh Smith <josh@batch.nz>
 * @since     1.0
 */
class Flo2CashWeb2Pay extends OffsiteGateway
{
     // Constants
    // =========================================================================
    /**
     * @event SetTransactionParticular
     *
     * ```php
     * use batchnz\flo2cash\events\SetTransactionParticular;
     * use batchnz\flo2cash\gateways\Flo2CashWeb2Pay;
     * use yii\base\Event;
     *
     * Event::on(Flo2CashWeb2Pay::class, Flo2CashWeb2Pay::EVENT_SET_TRANSACTION_PARTICULAR, function(SetTransactionParticular $e) {
     *     // Update transaction particular
     *     $e->particular = 'myParticular';
     * });
     * ```
     */
    const EVENT_SET_TRANSACTION_PARTICULAR = 'setTransactionParticular';

    /**
     * @event SetTransactionReference
     *
     * ```php
     * use batchnz\flo2cash\events\SetTransactionReference;
     * use batchnz\flo2cash\gateways\Flo2CashWeb2Pay;
     * use yii\base\Event;
     *
     * Event::on(Flo2CashWeb2Pay::class, Flo2CashWeb2Pay::EVENT_SET_TRANSACTION_REFERENCE, function(SetTransactionReference $e) {
     *     // Update transaction particular
     *     $e->particular = 'myParticular';
     * });
     * ```
     */
    const EVENT_SET_TRANSACTION_REFERENCE = 'setTransactionReference';

    // Properties
    // =========================================================================

    /**
     * Flo2Cash issued Account ID
     * Required
     * @var int
     */
    public $accountId;

    /**
     * Flo2Cash issued secret key
     * Your secret hash key is used to provide tamper proof message transfer between you and the Flo2Cash Web Payments application
     * @var string
     */
    public $secretKey;

    /**
     * Flo2Cash return option
     * The return option manages how your online payment page works once a customer makes a payment.
     * We recommend you use our default option 'Display in Web Payments' where your online the payment result is set to display within the payment and provides your customer with a link to go back to your site.
     * If you select 'Post to Return URL' then Web Payments will redirect the user to your site along with the transaction result data. If you select this option please make sure your Return URL is hosted under a valid SSL.
     * Must be one of "displayInWebPayments" or "returnToUrl"
     * @var string
     */
    public $returnOption;

    /**
     * The Merchant Notification Service (MNS) provides details of a processed transaction so that merchants can update their own system saccordingly.
     * It utilises a handshake verification procedure to avoid the spoofing aspect that could occur if using the fields posted back to the “return_url”.
     * @var boolean
     */
    // public $useMerchantNotificationService;

    /**
     * The URL to an image. Sets the image at the top of the payment page. The image can be of any height but must be a maximum of 600px wide and must be URLencoded.
     * The URL must end with  one  of  the  following  image  extensions “.jpg”, “.jpeg”, “.png”, “.bmp”, “.gif”.
     * Flo2Cash  recommends  that  you  provide  an image that is stored only on a secure (HTTPS) server. (See Customising the Flo2Cash Interface).
     * Optional
     * 1024 characters max
     * @var string
     */
    public $headerImage;

    /**
     * Sets the colour of the border underneath the header on the Flo2Cash hosted payment page. (See Customising the Flo2Cash Interface).
     * Value must be a 6-character hexadecimal value for the colour required.
     * Optional
     * 6 characters max
     * @var string
     */
    public $headerBottomBorder;

    /**
     * Sets the background colour of the header section on the Flo2Cash hosted payment page. (See Customising the Flo2Cash Interface).
     * Value must be a 6-characterhexadecimal value for the colour required.
     * Optional
     * 6 characters max
     * @var string
     */
    public $headerBackgroundColour;

    /**
     * 0 or 1 as to whether Web Payments should display the option for storing the card details upon a successful payment. 0 = do not show (default) 1 = show
     * Optional
     * @var int
     */
    public $storeCard = 0;

    /**
     * 0 or 1 as to whether Web Payments should display customer email receipt field. 1 = display (default) 0 = hide
     * Optional
     * @var int
     */
    public $displayCustomerEmail = 1;

    /**
     * If not set and the merchant is configured to accept more than one card payment types, the customer will be presented with a payment method selection page before completing the card payment.
     * For example, if the merchant accepts Visa, MasterCard and UnionPay, the customers will be presented with a payment method selection page presenting the two options of either paying with Visa/MasterCard or UnionPay card.
     * Merchants can, however, pre-select the preferred payment method using this parameter.
     * The following strings are supported values (both in lower case):
     *     -standard
     *     -unionpay
     *     -masterpass
     * If the value “standard” is passed, the customer will be directed to the standard Visa/MasterCard entry page, skipping the payment method selection page.
     * If the value “masterpass” is passed, the customer will be directed to the masterpass wallet processing flow, skipping the payment method selection page.
     * Similarly, if the value “unionpay” is passed, the customer will be directed to the UnionPay card transaction processing flow, skipping the payment method selection page.
     * Optional
     * 50 characters max
     * @var string
     */
    public $paymentMethod;

    /**
     * Whether to send cart info
     * @var boolean
     */
    public $sendCartInfo;

    /**
     * Passing this variable (value must be “1”) will allow you to collect customer information from the Flo2Cash Web Payments shopping cart page.
     * The customer information will then be posted back to your notification URL
     * Optional
     * @var int
     */
    public $customerInfoRequired;

    /**
     * Whether to use the integration in test mode
     * @var boolean
     */
    public $testMode = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('commerce', 'Flo2Cash Web Payments');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $settings = $this->getSettings();
        $headerImage = $settings['headerImage'];
        $headerImageElement = null;

        if( !empty($headerImage) && count($headerImage) > 0 ){
            $headerImageElement = [Craft::$app->getElements()->getElementById($headerImage[0])];
        }

        return Craft::$app->getView()->renderTemplate('commerce-flo2cash/web2pay/gatewaySettings', [
            'gateway' => $this,
            'returnOptions' => [
                Gateway::RETURN_OPTION_DISPLAY_IN_WEBPAYMENTS => 'Display in Web Payments',
                Gateway::RETURN_OPTION_RETURN_TO_URL => 'Return to URL'
            ],
            'paymentMethods' => [
                '' => 'All',
                Gateway::PAYMENT_METHOD_STANDARD => 'Visa/MasterCard',
                Gateway::PAYMENT_METHOD_UNIONPAY => 'UnionPay',
                Gateway::PAYMENT_METHOD_MASTERPASS => 'Masterpass'
            ],
            'headerImageSelectConfig' => [
                'id'                => 'headerImage',
                'name'              => 'headerImage',
                'jsClass'           => 'Craft.AssetSelectInput',
                'elementType'       => 'craft\\elements\\Asset',
                'elements'          => $headerImageElement,
                'criteria'          => ['kind' => ['image'], 'width' => '<= 600', 'enabledForSite' => true],
                'limit'             => 1,
                'viewMode'          => 'large',
                'selectionLabel'    => Craft::t('app','Select image'),
            ]
        ]);
    }

    /**
     * Triggers events to set request transaction particular and reference
     * @inheritdoc
     */
    protected function createPaymentRequest(Transaction $transaction, $card = null, $itemBag = null): array
    {
        $request = parent::createPaymentRequest($transaction, $card, $itemBag);

        $eventSetTransactionParticular = new SetTransactionParticular([
            'transaction' => $transaction
        ]);
        $this->trigger(self::EVENT_SET_TRANSACTION_PARTICULAR, $eventSetTransactionParticular);

        $eventSetTransactionReference = new SetTransactionReference([
            'transaction' => $transaction
        ]);
        $this->trigger(self::EVENT_SET_TRANSACTION_REFERENCE, $eventSetTransactionReference);

        // Set request properties
        $request['transactionParticular'] = $eventSetTransactionParticular->particular ?? '';
        $request['transactionReference'] = $eventSetTransactionReference->reference ?? $request['transactionReference'];

        return $request;
    }

    /**
     * @inheritdoc
     */
    public function populateRequest(array &$request, BasePaymentForm $paymentForm = null)
    {
        $craftRequest = Craft::$app->getRequest();

        $this->gateway()->setParticular($request['transactionParticular']);
        $this->gateway()->setReference($request['transactionReference']);

        // Populate parameters that come back from Flo2Cash via POST
        if( $craftRequest->getIsPost() && ($craftRequest->getOrigin() !== $craftRequest->getHostInfo()) ){
            foreach ($craftRequest->getBodyParams() as $key => $value) {
                $request[$key] = $value;
            }
        }
    }

    /**
     * Restricts the fields outputted when using toArray()
     * @author Josh Smith <josh@batch.nz>
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'handle',
            'sortOrder'
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createGateway(): AbstractGateway
    {
        // Extract the header image URL
        $headerImageUrl = '';
        if( is_array($this->headerImage) && count($this->headerImage) > 0 ) {
            $headerImage = Craft::$app->getElements()->getElementById($this->headerImage[0]);
            $headerImageUrl = empty($headerImage) ? '' : $headerImage->getUrl();
        }

        /** @var Gateway $gateway */
        $gateway = static::createOmnipayGateway($this->getGatewayClassName());
        $gateway->setAccountId(Craft::parseEnv($this->accountId));
        $gateway->setHeaderImage($headerImageUrl);
        $gateway->setHeaderBottomBorder($this->headerBottomBorder);
        $gateway->setHeaderBackgroundColour($this->headerBackgroundColour);
        $gateway->setStoreCard($this->storeCard);
        $gateway->setDisplayCustomerEmail($this->displayCustomerEmail);
        $gateway->setSecretKey(Craft::parseEnv($this->secretKey));
        $gateway->setPaymentMethod($this->paymentMethod);
        $gateway->setReturnOption($this->returnOption);
        $gateway->setUseShoppingCart($this->sendCartInfo);
        $gateway->setCustomerInfoRequired($this->customerInfoRequired);
        $gateway->setTestMode($this->testMode);

        return $gateway;
    }

    /**
     * @inheritdoc
     */
    protected function getGatewayClassName()
    {
        return '\\'.Gateway::class;
    }

    /**
     * @inheritdoc
     */
    protected function getItemBagClassName(): string
    {
        return Flo2CashItemBag::class;
    }

    /**
     * Override the parent method to set code as the description for Flo2Cash
     * @author Josh Smith <josh@batch.nz>
     * @param  Order  $order
     * @return array
     */
    protected function getItemListForOrder(Order $order): array
    {
        $items = parent::getItemListForOrder($order);

        // Assign the SKU as the code
        foreach ($order->getLineItems() as $i => $item) {
            $purchasable = $item->getPurchasable();
            $items[$i]['code'] = $purchasable->getSku();
        }

        return $items;
    }

    // Private Methods
    // =========================================================================
}
