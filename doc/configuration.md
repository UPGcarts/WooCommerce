# Configuration

## Payment Gateway settings
Your gateway provider needs to configure 2 URLs in their system to completely set up your account.
Callback URL: This URL will be called to inform the shop about successful payments and payment selections in the checkout process. This URL is vital for the payment process.
MNS URL: This URL will be called for asynchronous status updates for orders.

Once You have enabled the module you will find 'Callback URL' and 'MNS URL' in the module settings area. Please send these entries to your gateway provider and make sure they are accessible.

## Admin Settings
The settings are shown under WooCommerce->Settings->Checkout->Payment Module

* Hosted Payments Page : Settings for the main module
* Log Settings : Define what is logged and where it's saved.
* Merchant Settings : Configure the credentials provided by your payment provider and transaction type
* B2B Settings : Enable B2B transactions
* Callback URL : The callback url to be provided by the gateway provider
* MNS URL : The MNS url to be provided by the gateway provider
* MNS Cron URL : URL which you will need to set up as a cron job every 5 minutes
* Order Status Settings : For each MNS message what to change the order status to

## Admin Settings Section details

### Hosted Payments Page

* Enable/Disable : Should the module be enabled on the checkout
* Title : Name of the payment method shown in the checkout process

### Log Settings

* Log File Name : Name of the file in which the logs will be saved. Default directory for logs is /wp-content/wc-logs/. If a name is provided the log will be enabled.
* Log Level : Define how much should be logged. 'Debug' will log almost everything, while 'Emergency' will only log the most critical errors.


### Merchant Settings

* Mode : Select whether you want to use the sandbox/test environment or the live environment.
* Enable Autocapture : Select whether transactions should be captured automatically.
  o	Autocapture off: Each payment by customers has to be captured manually. This is usually done when the order is getting shipped. This is important for certain payment methods that require UPG to know when exactly the products were shipped. Dunning procedures depend on this for example.
  o	Autocapture on: Each payment by customers will be captured automatically by UPG as soon as the funds are available.
* Risk Class : If not specified for users and/or products, your payment provider will use this risk class by default for all transactions.
	o	Trusted Risk Class: No solvency checks will be done. The customer will always be able to select every payment method.
	o	Default Risk Class: Solvency checks will be executed, depending on your contract with UPG. Depending on the outcome the customer may be classified as high risk user and will only be able to use secure payment methods.
	o	High Risk Class: All customers are treated as high risk users by default and will only be able to use secure payment methods.
	It is recommended to use 'Default Risk Class'.
* Default Locale : The module will attempt to use the language of the store the customer is currently in. If this language is not supported it will use the language configured here.
* Merchant ID : The Merchant ID provided by gateway provider
* Password : Password provided by the gateway provider
* Store ID : Store ID provided by the gateway provider

### B2B Settings
* Enable/Disable : Enable or disable the transfer of business related data to your gateway provider. This data is used in solvency checks if agreed upon in your contract. This will display additional fields for the billing address in the checkout process.

### Callback URL
Your callback URL, which should be provided to your gateway provider

### MNS URL
Your MNS URL, which should be provided to your gateway provider

### MNS Cron URL
This URL needs to be executed by a cronjob. It is recommended to run the cronjob every 5 minutes.
This is required to process incoming transaction status notifications by your gateway provider.

### Order Status Settings

Here you define what your order status will be set to right after a successful or failed purchase.
Furthermore you can define to what status an order will be set to after receiving certain status notifications from your payment provider.
Notifications can contain both an order status and a transaction status:
Order status: This is the status of an 'order', which is basically a payment created by doing a capture. This means an order status can only be set in transaction status INPROGRESS and DONE, as a capture had to be executed before.
Transaction status: Status of the whole transaction. A transaction can contain multiple 'orders', which are created by doing captures.
For an explanation of each order/transaction status, see below.

* Return success status : When the callback is triggered what to set the order status to
* Return failure status : When the callback marks an order as a failure what to set the order status
* MNS PAID : Order status: You will receive this notification, if a capture was paid completely. Keep in mind that an order in your shop can have multiple captures, meaning this status notification does not necessarily mean the whole order is paid completely.
* MNS PAYPENDING : Order status: You will receive this notification, if a partial payment for a capture was made.
* MNS PAYMENTFAILED : Order status: You will receive this notification, if the payment for a capture failed.
* MNS CHARGEBACK : Order status: You will receive this notification, if the customer issued a chargeback on a payment made by him.
* MNS CLEARED : Order status: You will receive this notification, if the payment for a capture is cleared. This basically means it will show up in your next clearing file.
* MNS INDUNNING : Order status: You will receive this notification, if the customer did not pay his bill in time and the dunning process has started.
* MNS ACKNOWLEDGEPENDING : Transaction status: You will receive this notification, if a transaction was successfully started. If the order in your shop stays in this status with no follow-up notification, it is advised to contact your payment provider, as their system was unable to reach your shop in the checkout process.
* MNS FRAUDPENDING : Transaction status: You will receive this notification, if a transaction was created and our system recognises it to be a potential fraud case. Your payment provider will manually check this transaction.
* MNS FRAUDCANCELLED : Transaction status: You will receive this notification, if a transaction was cancelled due to fraud.
* MNS CIAPENDING : Transaction status: You will receive this notification, if your payment provider is waiting for the customers money to arrive on their bank account. This only applies to cash in advance payments.
* MNS CANCELLED / EXPIRED : Transaction status: You will one of these notifications, if the transaction was either manually cancelled by the merchant, or the transaction expired. A transaction can expire, when either the customer does not finish his payment selection, or the transaction stays in status 'ACKNOWLEDGEPENDING' for a certain amount of time.
* MNS MERCHANTPENDING : Transaction status: You will receive this notification, if your payment provider is waiting for you to create a capture for this transaction.
* MNS INPROGRESS : Transaction status: You will receive this notification, if a payment by the customer arrived, but not all captures of the transaction are completely paid.
* MNS DONE : Transaction status: You will receive this notification, if all captures of the transaction are completely paid.

## Product Settings

In the product edit section the field 'Risk class' is introduced. If this is set then the payment module will send the risk class for that product.
This allows you do mark certain products as high risk products, resulting in a customers only being able to pay with secure payment methods when purchasing these.

## Customer Settings

In the edit user section the field 'Customer Risk Class' is introduced for customers. Here you to mark customers with a certain risk class, which overrides the default value.
This allows you to mark regular customers as 'Trusted', meaning they will be able to use all available payment methods, independent of the product risk class. You may also mark customers as high risk customers, allowing them to only pay with secure payment methods by default.
