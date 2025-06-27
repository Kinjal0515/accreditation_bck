<?php
namespace App\Jobs;

use App\Models\UserCard;
use App\Models\WhatsappApi;
use App\Services\SmsService;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUserNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function handle(SmsService $smsService, WhatsappService $whatsappService)
    {
        if ($this->user->approval_status != 1) return;

        $buttonValue = UserCard::where('user_id', $this->user->id)->first();
        $filename = $buttonValue ? basename($buttonValue->card_url) : null;

        $whatsappTemplate = WhatsappApi::where('title', 'Acc Ready')->first();
        $whatsappTemplateName = $whatsappTemplate->template_name ?? '';

        $data = (object)[
            'name' => $this->user->name,
            'number' => $this->user->number,
            'company_number' => $this->user->comp->number ?? '',
            'event_name' => $this->user->userOrganisation->event_name ?? '',
            'button_value' => $filename,
            'templateName' => 'Company Register',
            'whatsappTemplateData' => $whatsappTemplateName,
        ];

        $smsService->send($data);
        $whatsappService->send($data);
    }
}
