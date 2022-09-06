# WHMCS Cron Capture Payment Hook

A hook that runs on the cron to allow multiple payment capture attempts per day.

The follow variables can be configured:

```$daysBeforeDueDateToCapture``` - How many days before the due date you wish to capture payment
e.g. if set to 3 and todays date is 6th Sept, only unpaid invoice due on the 9th Sept will be checked

```$captureTimes``` - What hours of the day you wish to capture payment (e.g. '06' is 6am, '18' is 6pm)

```$allowedPaymentMethods``` - If you only want to capture payments on certain payment methods, add them here (e.g. 'stripe', 'paypalcheckout')

```$logErrors``` - If enabled and the capture fails, log the error in the clients log


## How to install

1. Copy the ```includes``` folder to your root WHMCS directory.

## Contributions

Feel free to fork the repo, make changes, then create a pull request! For ideas on what you can help with, check the project issues.