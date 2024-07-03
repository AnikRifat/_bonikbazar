<?php

namespace App\Services;

use App\Models\Setting;
use RuntimeException;
use Throwable;

class NotificationService {
    /**
     * @param array $registrationIDs
     * @param string $title
     * @param string $message
     * @param array $customBodyFields
     * @return false|mixed
     * @throws Throwable
     */
    public static function sendFcmNotification(array $registrationIDs, string $title, string $message, array $customBodyFields = []) {
        try {
            $fcm_key = Setting::select('value')->where('name', 'fcm_key')->first()->value;
            $registrationIDs_chunks = array_chunk($registrationIDs, 1000);

            $unregisteredIDs = array();
            if (!count($registrationIDs_chunks)) {
                return false;
            }
            $result = [];
            foreach ($registrationIDs_chunks as $registrationIDsChunk) {
                $fcmMsg = [
                    'title'        => $title,
                    'message'      => $message,
                    'body'         => $message,
                    'type'         => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound'        => 'default',
                    ...$customBodyFields
                ];
                $fcmFields = array(
                    'registration_ids' => $registrationIDsChunk,
                    'priority'         => 'high',
                    'notification'     => $fcmMsg,
                    'data'             => $fcmMsg
                );

                $headers = array(
                    'Authorization: key=' . $fcm_key,
                    'Content-Type: application/json'
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields, JSON_THROW_ON_ERROR));

                $get_result = curl_exec($ch);

                curl_close($ch);
                $result[] = json_decode($get_result, true, 512, JSON_THROW_ON_ERROR);


//                if (isset($result['results'])) {
//                    foreach ($result['results'] as $index => $response) {
//                        if (isset($response['error']) && $response['error'] == 'NotRegistered') {
//                            $unregisteredIDs[] = $registrationIDsChunk[$index];
//                        }
//                    }
//                }
            }

//            if (count($unregisteredIDs)) {
//                User::whereIn('fcm_id', $unregisteredIDs)->delete();
//            }
            return $result;
        } catch (Throwable $th) {
            throw new RuntimeException($th);
        }
    }
}
