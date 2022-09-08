<?php

use WHMCS\Carbon;
use WHMCS\Database\Capsule;

/**
 * WHMCS Cron Capture Payment
 *
 * A hook that runs on the cron to allow multiple payment capture attempts per day
 *
 * @package    WHMCS
 * @author     Lee Mahoney <lee@leemahoney.dev>
 * @copyright  Copyright (c) Lee Mahoney 2022
 * @license    MIT License
 * @version    1.0.1
 * @link       https://leemahoney.dev
 */

if (!defined('WHMCS')) {
    die('You cannot access this file directly.');
}

function cron_capture_payment($vars) {

    # How many days before the due date you wish to capture payment
    # e.g. if set to 3 and todays date is 6th Sept, only unpaid invoice due on the 9th Sept will be checked
    $daysBeforeDueDateToCapture = 0;

    # What hours of the day you wish to capture payment (e.g. '06' is 6am, '18' is 6pm)
    $captureHours = ['00', '06', '12', '18'];

    # Which minute after the hour you wish this check to run at (e.g. if your cron is set to run every 5 minutes, can set this to 5, 10, 15, 20, 25, etc...)
    $captureMinute = 10;

    # If you only want to capture payments on certain payment methods, add them here (e.g. 'stripe', 'paypalcheckout')
    $allowedPaymentMethods = [];

    # If enabled and the capture fails, log the error in the clients log
    $logErrors = true;

    /* ------------------------------------------ */
    /* ONLY EDIT VARIABLES ABOVE THIS LINE        */
    /* ------------------------------------------ */

    # Grab the current time and date
    $currentTime    = Carbon::now()->format('H');
    $currentMinute  = Carbon::now()->format('i');
    $currentDay     = Carbon::now()->format('Y/m/d');

    # Check if the current hour is in the $captureHours array
    if (in_array($currentTime, $captureHours) && $currentMinute == $captureMinute) {

        # Calculate the due date based on the $daysBeforeDueDateToCapture variable
        $theDueDate = Carbon::now()->addDays($daysBeforeDueDateToCapture)->format('Y-m-d');

        # Grab all unpaid invoices that match the due date
        $invoices = Capsule::table('tblinvoices')->where('duedate', $theDueDate)->where('status', 'Unpaid')->get();

        # Loop through the invoices
        foreach ($invoices as $invoice) {

            # If the $allowedPaymentMethods is not empty, check that the invoice's payment method is in it
            if (!empty($allowedPaymentMethods) && !in_array($invoice->paymentmethod, $allowedPaymentMethods)) {
                return;
            }

            # Attempt to capture payment
            $result = localAPI('CapturePayment', [
                'invoiceid' => $invoice->id
            ]);
            
            # If $logErrors is true and an error is present, log it to the clients log
            if ($result['result'] === 'error' && $logErrors) {
                logActivity("Automatic payment capture hook failed on invoice #{$invoice->id}: {$result['message']}", $invoice->userid);
            }

        }

    }

}

add_hook('AfterCronJob', 1, 'cron_capture_payment');
