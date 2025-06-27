<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function send($data)
    {

        $number = preg_replace('/[^0-9]/', '', $data->number);
        $modifiedNumber = strlen($number) === 10 ? '91' . $number : $number;

        $template = $data->whatsappTemplateData ?? '';
        $mediaurl = $data->button_value ?? '';

        $admin = User::role('Admin', 'api')->with('whatsappConfig')->first();
        if ($admin && $admin->whatsappConfig && isset($admin->whatsappConfig[0])) {
            $apiKey = $admin->whatsappConfig[0]->api_key ?? null;
        } else {
            return ['error' => 'Admin WhatsApp configuration not found'];
        }

        if (!$apiKey) {
            return ['error' => 'API Key missing'];
        }

        // Template values
        $value = [
            $data->name ?? '',
            $data->event_name ?? '',
            'sms for you',
            '2025-06-27 15:26:32',        
        ];

        // API Call
        $whatsappApi = "https://waba.smsforyou.biz/api/send-messages";
        $params = [
            'apikey'     => $apiKey,
            'to'         => $modifiedNumber,
            'type'       => 'T',
            'tname'      => $template,
            'values'     => implode(',', $value),
            'button_value'  => $mediaurl
        ];
        // Log::info('Sending WhatsApp Message', ['response' => $params]);
        $response = Http::get($whatsappApi, $params);
        // Log::info('Sending WhatsApp Message', ['response' => $response->body()]);

        return $response->successful()
            ? ['message' => 'WhatsApp sent successfully', 'url' => $whatsappApi . '?' . http_build_query($params)]
            : ['error' => 'Failed to send WhatsApp', 'details' => $response->body()];
    }
}
