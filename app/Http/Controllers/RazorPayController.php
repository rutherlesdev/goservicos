<?php


/*
 * File name: RazorPayController.php
 * Last modified: 2021.04.18 at 14:01:27
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2021
 */

namespace App\Http\Controllers;

use Exception;
use Flash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Razorpay\Api\Api;

class RazorPayController extends ParentBookingController
{

    /**
     * @var Api
     */
    private $api;
    private $currency;

    public function __init()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
        $this->currency = config('installer.demo_app') ? 'INR' : setting('default_currency_code', 'INR');
    }


    public function index()
    {
        return view('home');
    }


    public function checkout(Request $request)
    {
        $this->booking = $this->bookingRepository->findWithoutFail($request->get('booking_id'));
        if (!empty($this->booking)) {
            try {
                $razorPayCart = $this->getBookingData();

                $razorPayBooking = $this->api->order->create($razorPayCart);
                $fields = $this->getRazorPayFields($razorPayBooking);
                //url-ify the data for the POST
                $fields_string = http_build_query($fields);

                //open connection
                $ch = curl_init();

                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/checkout/embedded');
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                $result = curl_exec($ch);
                if ($result === true) {
                    die();
                }
            } catch (Exception $e) {
                Flash::error($e->getMessage());
                return redirect(route('payments.failed'));
            }
        } else {
            Flash::error("Error processing RazorPay payment for your booking");
            return redirect(route('payments.failed'));
        }
        return null;
    }

    /**
     * Set cart data for processing payment on PayPal.
     *
     *
     * @throws Exception
     */
    private function getBookingData(): array
    {
        $data = [];
        $amountINR = $this->booking->getTotal();
        $booking_id = $this->paymentRepository->all()->count() + 1;
        $data['amount'] = (int)($amountINR * 100);
        $data['payment_capture'] = 1;
        $data['currency'] = $this->currency;
        $data['receipt'] = $booking_id . '_' . date("Y_m_d_h_i_sa");

        return $data;
    }

    /**
     * @param $razorPayBooking
     * @return array
     */
    private function getRazorPayFields($razorPayBooking): array
    {

        $fields = array(
            'key_id' => config('services.razorpay.key', ''),
            'order_id' => $razorPayBooking['id'],
            'name' => $this->booking->e_provider->name,
            'description' => $this->booking->e_service->name,
            'image' => $this->booking->e_service->getFirstMediaUrl('image', 'thumb'),
            'prefill' => [
                'name' => $this->booking->user->name,
                'email' => $this->booking->user->email,
                'contact' => config('installer.demo_app') ? "+9102228811844" : $this->booking->user->phone_number,
            ],
            'notes' => [
                'address' => $this->booking->address,
            ],
            'callback_url' => url('payments/razorpay/pay-success', ['booking_id' => $this->booking->id]),

        );

        if ($this->currency !== 'INR') {
            $fields['display_amount'] = $this->booking->getTotal();
            $fields['display_currency'] = $this->currency;
        }
        return $fields;
    }

    /**
     * @param int $bookingId
     * @param int $deliveryAddressId
     * @param Request $request
     * @return JsonResponse|RedirectResponse|Redirector
     */
    public function paySuccess(Request $request, int $bookingId)
    {
        $data = $request->all();

        $description = $this->getPaymentDescription($data);
        $this->booking = $this->bookingRepository->findWithoutFail($bookingId);
        $this->paymentMethodId = 2; // Paypal method
        if (!empty($this->booking)) {
            if ($request->hasAny(['razorpay_payment_id', 'razorpay_signature'])) {

                $this->createBooking();

                return redirect(url('payments/razorpay'));
            }
        }
        Flash::error("Error processing RazorPay payment for your booking");
        return redirect(route('payments.failed'));

    }

    /**
     * @param array $data
     * @return string
     */
    private function getPaymentDescription(array $data): string
    {
        $description = "Id: " . $data['razorpay_payment_id'] . "</br>";
        $description .= trans('lang.booking') . ": " . $data['razorpay_order_id'];
        return $description;
    }

}
