# SIP2 Connection Tester

This app tests SIP2 connections by making a SIP2 "Item Information" call on an arbitrary item by barcode. It also supports issuing Checkin calls.

It is a PHP app wrapped in Node, able to be run as an AWS Lambda. Use of Node-wrapped PHP is intentional to match the three apps that currently use SIP2 ([CheckoutService](https://github.com/NYPL/checkout-request-service), [CheckinRequestService](https://github.com/NYPL/checkin-request-service), and [RefileRequestService](https://github.com/NYPL/refile-request-service)).

## Running Locally

> Note: Local use hasn't worked for a while due to firewall rules around Sierra

To test SIP2 connections locally:

```
sam local invoke --template sam.template.yml --event ./event.json
```

## Running deployed code

To test SIP2 connections via AWS Lambda (i.e. to test connections from a VPC/subnet config):

 * Head to [deployed lambda](https://console.aws.amazon.com/lambda/home?region=us-east-1#/functions/sip2-connection-tester/versions/$LATEST?tab=configuration)
 * Select the shared 'connectiontest' event. Optionally edit the `barcode`

To perform a SIP2 checkin, choose shared 'checkintest' event. Edit the `barcode` and optionally add a `location`.

Note that env var `SIP2_HOSTNAME` determines what Sierra instance you connect to.

### Event supported properties:
 - `barcode`: The barcode to query / checkin
 - `doCheckin`: If set to true, app will perform a checkin
 - `location`: The location to use when performing a checkin.

## Contributing

 * Make changes
 * `./build.sh`
 * aws lambda update-function-code --function-name sip2-connection-tester --zip-file fileb://./sip2-connection-tester.zip --profile nypl-digital-dev
