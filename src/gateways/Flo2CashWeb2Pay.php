<?php

namespace batchnz\flo2cash\gateways;

use Craft;
use craft\commerce\base\RequestResponseInterface;
use craft\commerce\errors\PaymentException;
use craft\commerce\models\payments\BasePaymentForm;
use craft\commerce\models\Transaction;
use batchnz\flo2cash\PayPalExpressBundle;
use craft\commerce\omnipay\base\OffsiteGateway;
use craft\web\View;
use Omnipay\Common\AbstractGateway;
use Omnipay\Omnipay;

/**
 * Web2Pay represents the Hosted Payment Pages
 *
 * @author    Josh Smith <josh@batch.nz>
 * @since     1.0
 */
class Flo2CashWeb2Pay extends OffsiteGateway
{
    // Properties
    // =========================================================================

    /**
     * Defines the Web Payments integration service. Always use “_xclick” for Web Payments Standard Payment.
     * Required
     * @var string
     */
    public $cmd = '_xclick';

    /**
     * Flo2Cash issued Account ID
     * Required
     * @var int
     */
    public $accountId;

    /**
     * The transaction amount in NZ dollars. Must be a positive value.
     * Required
     * @var float
     */
    // public $amount;

    /**
     * Description of item, not stored by Flo2Cash.
     * Required
     * @var string
     */
    // public $item_name;

    /**
     * Merchant defined value stored with the transaction.
     * Optional
     * 50 characters max
     * @var string
     */
    // public $reference;

    /**
     * Merchant defined value stored with the transaction.
     * Optional
     * 50 characters max
     * @var string
     */
    // public $particular;

    /**
     * The URL that the customer will be sent to on completion of the payment. This must be a publicly accessible URL.
     * Required
     * 1024 characters max
     * @var string
     */
    // public $return_url;

    /**
     * If provided, this URL will be used in conjunction with Flo2Cashs Merchant Notification Service (MNS). (See MNSfor details) This must be a publicly accessible URL.
     * Optional
     * 1024 characters max
     * @var string
     */
    // public $notification_url;

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
     * Merchant defined value that you can use to identify your transaction.
     * Any value passed in will be posted back to the notification_url (See MNS).
     * This is a pass-throughfield that is never presented to your customer. Flo2Cash will not store this value.
     * Optional
     * 1024 characters max
     * @var string
     */
    // public $custom_data;

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
     * This is a SHA1 hash of the data that you are passing plus your secret hash key (explained above).
     * Please see the appendix (Flo2Cash Calculating the merchant_verifier input parameter value for Standard Payments) for sample code on how to calculate this field.
     * Required
     * @var string
     */
    // public $merchant_verifier;

    /**
     * @var string
     */
    public $testMode;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('commerce', 'Flo2Cash Web2Pay');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('commerce-flo2cash/web2pay/gatewaySettings', [
            'gateway' => $this,
            'headerImageSelectConfig' => [
                'id'                => 'headerImage',
                'name'              => 'headerImage',
                'jsClass'           => 'Craft.AssetSelectInput',
                'elementType'       => 'craft\\elements\\Asset',
                'elements'          => $this->getSettings()['headerImage'] && count($this->getSettings()['headerImage']) ? [Craft::$app->getElements()->getElementById($this->getSettings()['headerImage'][0])] : null,
                'criteria'          => ['kind' => ['image'], 'width' => '<= 600', 'enabledForSite' => true],
                'limit'             => 1,
                'viewMode'          => 'large',
                'selectionLabel'    => Craft::t('app','Select image'),
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function populateRequest(array &$request, BasePaymentForm $paymentForm = null)
    {
        echo '<pre> "populatingRequest": '; print_r("populatingRequest"); echo '</pre>'; die();
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createGateway(): AbstractGateway
    {
        /** @var Gateway $gateway */
        $gateway = static::createOmnipayGateway($this->getGatewayClassName());

        $gateway->setUsername(Craft::parseEnv($this->username));
        $gateway->setPassword(Craft::parseEnv($this->password));
        $gateway->setSignature(Craft::parseEnv($this->signature));
        $gateway->setTestMode($this->testMode);
        $gateway->setSolutionType($this->solutionType);
        $gateway->setLandingPage($this->landingPage);
        $gateway->setBrandName($this->brandName);
        $gateway->setHeaderImageUrl($this->headerImageUrl);
        $gateway->setLogoImageUrl($this->logoImageUrl);
        $gateway->setBorderColor($this->borderColor);

        return $gateway;
    }

    /**
     * @inheritdoc
     */
    protected function getGatewayClassName()
    {
        return '\\'.Gateway::class;
    }

    // Private Methods
    // =========================================================================
}
