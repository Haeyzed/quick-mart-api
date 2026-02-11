<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * WhatsappSetting Model
 *
 * Represents WhatsApp Business API configuration settings.
 *
 * @property int $id
 * @property string $phone_number_id
 * @property string $business_account_id
 * @property string $permanent_access_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WhatsappSetting extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'phone_number_id',
        'business_account_id',
        'permanent_access_token',
    ];

    /**
     * Base URL for Facebook Graph API.
     */
    private string $baseUrl = 'https://graph.facebook.com/v22.0';

    /**
     * Send WhatsApp message to multiple phone numbers.
     *
     * @param  array<int, string>  $phoneNumbers
     * @param  array<string, mixed>|string  $messageContent
     * @return array<string, mixed>
     */
    public function sendMessage(array $phoneNumbers, string $type, array|string $messageContent): array
    {
        $messageEndpoint = "{$this->baseUrl}/{$this->phone_number_id}/messages";
        $headers = [
            'Authorization' => "Bearer {$this->permanent_access_token}",
            'Accept' => 'application/json',
        ];

        try {
            $payload = [
                'messaging_product' => 'whatsapp',
            ];

            if ($type === 'template') {
                $payload['type'] = 'template';
                $payload['template'] = [
                    'name' => $messageContent['name'],
                    'language' => ['code' => $messageContent['lang_code']],
                ];
            } elseif ($type === 'text') {
                $payload['type'] = 'text';
                $payload['text'] = ['body' => $messageContent];
            } elseif (in_array($type, ['image', 'document'], true)) {
                $file = $messageContent['file'];
                $originalName = $file->getClientOriginalName();

                $uploadResponse = Http::withOptions(['verify' => false])
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->permanent_access_token}",
                    ])->attach(
                        'file',
                        file_get_contents($file->getRealPath()),
                        $originalName
                    )->post("{$this->baseUrl}/{$this->phone_number_id}/media", [
                        'messaging_product' => 'whatsapp',
                    ]);

                if (! $uploadResponse->successful()) {
                    return [
                        'success' => false,
                        'message' => 'Failed to upload media to WhatsApp',
                        'body' => $uploadResponse->body(),
                    ];
                }

                $mediaId = $uploadResponse->json('id');
                if (! $mediaId) {
                    return [
                        'success' => false,
                        'message' => 'Media ID not returned from WhatsApp',
                    ];
                }

                $payload['type'] = $type;
                $payload[$type] = ['id' => $mediaId];

                if ($type === 'document') {
                    $payload[$type]['filename'] = $originalName;
                }

                if (! empty($messageContent['caption'])) {
                    $payload[$type]['caption'] = $messageContent['caption'];
                }
            }

            $results = [];

            foreach ($phoneNumbers as $phoneNumber) {
                $payload['to'] = $phoneNumber;
                dispatch(function () use ($headers, $messageEndpoint, $payload, $phoneNumber, &$results): void {
                    $response = Http::withOptions(['verify' => false])
                        ->withHeaders($headers)
                        ->post($messageEndpoint, $payload);

                    $results[$phoneNumber] = $response->json();
                });
            }

            return [
                'success' => true,
                'message' => 'Message sent successfully',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get message templates from Facebook.
     *
     * @return array<string, mixed>
     */
    public function getTemplates(): array
    {
        $url = "{$this->baseUrl}/{$this->business_account_id}/message_templates";
        $headers = ['Authorization' => "Bearer {$this->permanent_access_token}"];

        $response = Http::withOptions(['verify' => false])->withHeaders($headers)->get($url);

        if ($response->successful()) {
            return $response->json('data') ?? [];
        }

        return ['error' => 'Failed to fetch templates'];
    }

    /**
     * Delete template from Facebook.
     *
     * @return array<string, mixed>
     */
    public function deleteTemplate(string $name): array
    {
        $url = "{$this->baseUrl}/{$this->business_account_id}/message_templates?name={$name}";
        $headers = ['Authorization' => "Bearer {$this->permanent_access_token}"];

        $response = Http::withOptions(['verify' => false])->withHeaders($headers)->delete($url);

        if ($response->successful()) {
            return ['success' => true, 'message' => 'Template deleted successfully'];
        }

        return [
            'success' => false,
            'message' => 'Failed to delete template',
            'response' => $response->json(),
        ];
    }
}
