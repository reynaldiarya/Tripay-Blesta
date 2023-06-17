# Tripay Gateway

This is a nonmerchant gateway for Blesta that integrates with [Tripay](https://tripay.co.id/register).

## Install the Gateway

1. Upload the source code to a /components/gateways/nonmerchant/ directory within
your Blesta installation path.

    For example:

    ```
    /var/www/html/blesta/components/gateways/nonmerchant/
    ```

2. Log in to your admin Blesta account and navigate to
> Settings > Payment Gateways

3. Find the Tripay gateway and click the "Install" button to install it

4. Enter data Merchant Code, Api Key, Private Key

5. Setting the Tripay Callback by the way
> Login to your Tripay account > Merchant > 'Opsi' > 'Edit'
    In the URL Callback, enter https://domain.com/blesta_directory/callback/gw/1/tripay_channel(one of the active channels, ex: tripay_bni_va, tripay_qrisc, tripay_shopeepay, etc). For example: 'https://domain.com/blesta_directory/callback/gw/1/tripay_bca_va'.

6. You're done!

## Change payment name and button

1. Open tripay_channel.php(each name on the channel will be different according to the channel name) in folder language/en_us.For example: '/var/www/html/blesta/components/gateways/nonmerchant/tripay_bca_va/language/en_us'.

2. Change '$lang['TripayChannel.name'] = "Tripay Channel"' to change the payment name. You just need to change "Tripay Channel".

3. Change '$lang['TripayChannel.buildprocess.submit'] = "Pay with Tripay Channel"' to change the payment button. You just need to change "Pay with Tripay Channel".



### Blesta Compatibility

|Blesta Version|Module Version|
|--------------|--------------|
|>= v4.9.0|v1.0.1|
