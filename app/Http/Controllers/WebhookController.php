<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PaymentConfiguration;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\UserPurchasedPackage;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Webhook;
use Throwable;


class WebhookController extends Controller {
    public function stripe() {
        $payload = @file_get_contents('php://input');
        Log::info(PHP_EOL . "----------------------------------------------------------------------------------------------------------------------");
        try {
            // Verify webhook signature and extract the event.
            // See https://stripe.com/docs/webhooks/signatures for more information.
            $data = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);

            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

            // You can find your endpoint's secret in your webhook settings
            $paymentConfiguration = PaymentConfiguration::select('webhook_secret_key')->where('payment_method', 'stripe')->first();
            $endpoint_secret = $paymentConfiguration['webhook_secret_key'];
            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );

            $metadata = $event->data->object->metadata;


//            // Use this lines to Remove Signature verification for debugging purpose
//            $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
//            $metadata = (array)$event->data->object->metadata;

            Log::info("Stripe Webhook : ", [$event]);

            // handle the events
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    Log::info("payment_intent.succeeded called successfully");
                    $paymentTransactionData = PaymentTransaction::where('id', $metadata['payment_transaction_id'])->first();
                    if ($paymentTransactionData == null) {
                        Log::error("Stripe Webhook : Payment Transaction id not found");
                        break;
                    }

                    if ($paymentTransactionData->status == "succeed") {
                        Log::info("Stripe Webhook : Transaction Already Succeed");
                        break;
                    }

                    DB::beginTransaction();
                    $paymentTransactionData->update(['payment_status' => "succeed"]);

                    $user = User::find($metadata['user_id']);
                    $package = Package::find($metadata['package_id']);

                    if (!empty($package)) {
                        UserPurchasedPackage::create([
                            'package_id'  => $metadata['package_id'],
                            'start_date'  => Carbon::now(),
                            'end_date'    => $package->duration != 0 ? Carbon::now()->addDays($package->duration) : NULL,
                            'user_id'     => $metadata['user_id'],
                            'total_limit' => $package->item_limit == "unlimited" ? null : $package->item_limit,
                        ]);
                    }

                    $title = "Package Purchased";
                    $body = 'Amount :- ' . $paymentTransactionData->amount;

                    NotificationService::sendFcmNotification([$user->fcm_id], $title, $body, ['type' => 'payment']);
                    http_response_code(200);
                    DB::commit();
                    break;
                case
                'payment_intent.payment_failed':
                    $paymentTransactionData = PaymentTransaction::find($metadata['payment_transaction_id']);
                    if (!$paymentTransactionData) {
                        Log::error("Stripe Webhook : Payment Transaction id not found --->");
                        break;
                    }

                    $paymentTransactionData->update(['payment_status' => "failed"]);

                    http_response_code(400);
                    $user = User::where('id', $metadata['user_id'])->first();
                    $body = 'Amount :- ' . $paymentTransactionData->amount;
                    $type = 'payment';
                    NotificationService::sendFcmNotification([$user->fcm_id], 'Package Payment Failed', $body, ['type' => 'payment']);
                    break;
                default:
                    Log::error('Stripe Webhook : Received unknown event type');
            }
        } catch (UnexpectedValueException) {
            // Invalid payload
            echo "Stripe Webhook : Payload Mismatch";
            Log::error("Stripe Webhook : Payload Mismatch");
            http_response_code(400);
            exit();
        } catch (SignatureVerificationException) {
            // Invalid signature
            echo "Stripe Webhook : Signature Verification Failed";
            Log::error("Stripe Webhook : Signature Verification Failed");
            http_response_code(400);
            exit();
        } catch
        (Throwable $e) {
            DB::rollBack();
            Log::error("Stripe Webhook : Error occurred", [$e->getMessage() . ' --> ' . $e->getFile() . ' At Line : ' . $e->getLine()]);
            http_response_code(400);
            exit();
        }
    }
}

