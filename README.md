# Tripay Gateway

This is a nonmerchant gateway for Blesta that integrates with [Tripay](https://tripay.co.id/register).

## Install the Gateway

1. Upload the source code to a /components/gateways/nonmerchant/ directory within
your Blesta installation path.

    For example:

    ```
    /var/www/html/blesta/components/nonmerchant/
    ```

2. Log in to your admin Blesta account and navigate to
> Settings > Payment Gateways

3. Find the Tripay gateway and click the "Install" button to install it

4. Enter data Merchant Code, Api Key, Private Key

5. Setting the Tripay Callback by the way
> Login to your Tripay account > Merchant > 'Opsi' > 'Edit'
    In the URL Callback, enter https://domain.com/blesta_directory/callback/gw/1/tripay_bca_va(one of the active channels, ex: tripay_bni_va, tripay_qrisc, tripay_shopeepay, etc).

6. You're done!

### Blesta Compatibility

|Blesta Version|Module Version|
|--------------|--------------|
|>= v4.9.0|v1.0.0|
