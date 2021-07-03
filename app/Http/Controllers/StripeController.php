<?php
/*
 * File name: StripeController.php
 * Last modified: 2021.05.07 at 19:12:31
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

class StripeController extends ParentBookingController
{

    private $stripePaymentMethodId;

    public function __init()
    {

    }

    public function index()
    {
        return view('home');
    }

    public function checkout(Request $request)
    {
        $this->booking = $this->bookingRepository->findWithoutFail($request->get('booking_id'));
        if (empty($this->booking)) {
            Flash::error("Error processing Stripe payment for your booking");
            return redirect(route('payments.failed'));
        }
        return view('payment_methods.stripe_charge', ['booking' => $this->booking]);
    }

    public function paySuccess(Request $request, int $bookingId, string $paymentMethodId)
    {
        $this->booking = $this->bookingRepository->findWithoutFail($bookingId);
        $this->stripePaymentMethodId = $paymentMethodId;

        if (empty($this->booking)) {
            Flash::error("Error processing Stripe payment for your booking");
            return redirect(route('payments.failed'));
        } else {
            try {
                $stripeCart = $this->getBookingData();
                $intent = PaymentIntent::create($stripeCart);
                $intent = PaymentIntent::retrieve($intent->id);
                $intent = $intent->confirm();
                Log::info($intent->status);
                if ($intent->status == 'succeeded') {
                    $this->paymentMethodId = 7; // Stripe method
                    $this->createBooking();
                }
                return $this->sendResponse($intent, __('lang.saved_successfully'));
            } catch (ApiErrorException $e) {
                return $this->sendError($e->getMessage());
            }
        }
    }

    /**
     * Set cart data for processing payment on Stripe.
     */
    private function getBookingData(): array
    {
        $data = [];
        $amount = $this->booking->getTotal();
        $data['amount'] = (int)($amount * 100);
        $data['payment_method'] = $this->stripePaymentMethodId;
        $data['currency'] = setting('default_currency_code');

        return $data;
    }
}
