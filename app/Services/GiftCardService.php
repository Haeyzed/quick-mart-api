<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\GiftCardCreate;
use App\Mail\GiftCardRecharge as GiftCardRechargeMail;
use App\Models\GeneralSetting;
use App\Models\GiftCard;
use App\Models\GiftCardRecharge;
use App\Models\MailSetting;
use App\Traits\MailInfo;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * GiftCardService
 *
 * Handles all business logic for gift card operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class GiftCardService extends BaseService
{
    use MailInfo;

    /**
     * Get paginated list of gift cards with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, customer_id, user_id, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<GiftCard>
     */
    public function getGiftCards(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return GiftCard::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                isset($filters['customer_id']),
                fn($query) => $query->where('customer_id', $filters['customer_id'])
            )
            ->when(
                isset($filters['user_id']),
                fn($query) => $query->where('user_id', $filters['user_id'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where('card_no', 'like', '%' . $filters['search'] . '%')
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single gift card by ID.
     *
     * @param int $id Gift Card ID
     * @return GiftCard
     */
    public function getGiftCard(int $id): GiftCard
    {
        return GiftCard::findOrFail($id);
    }


    /**
     * Create a new gift card.
     *
     * @param array<string, mixed> $data Validated gift card data
     * @return GiftCard
     */
    public function createGiftCard(array $data): GiftCard
    {
        return $this->transaction(function () use ($data) {
            // Auto-generate card_no if not provided
            if (empty($data['card_no'])) {
                $data['card_no'] = GiftCard::generateCode();
            }

            // Normalize data to match database schema
            $data = $this->normalizeGiftCardData($data);

            // Handle user/customer logic
            if (isset($data['user']) && $data['user']) {
                $data['customer_id'] = null;
            } else {
                $data['user_id'] = null;
            }
            unset($data['user']); // Remove helper field

            // Set created_by (business logic - Auth::id())
            $data['created_by'] = Auth::id();

            $giftCard = GiftCard::create($data);

            // Send email notification
            $this->sendGiftCardCreateEmail($giftCard);

            return $giftCard;
        });
    }

    /**
     * Normalize gift card data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeGiftCardData(array $data): array
    {
        // Set default is_active if not provided (matching old controller)
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        } else {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        // Set expense to 0 if not provided
        if (!isset($data['expense'])) {
            $data['expense'] = 0;
        }

        return $data;
    }

    /**
     * Send gift card creation email notification.
     *
     * @param GiftCard $giftCard
     * @return void
     */
    private function sendGiftCardCreateEmail(GiftCard $giftCard): void
    {
        $mailSetting = MailSetting::default()->first();
        if (!$mailSetting) {
            abort(Response::HTTP_BAD_REQUEST, 'Mail settings are not configured. Please contact the administrator.');
        }

        $recipient = null;
        $email = null;
        $name = null;

        // Get recipient based on user_id or customer_id
        if ($giftCard->user_id) {
            $giftCard->load('user');
            if ($giftCard->user && $giftCard->user->email) {
                $recipient = $giftCard->user->email;
                $name = $giftCard->user->name;
            }
        } elseif ($giftCard->customer_id) {
            $giftCard->load('customer');
            if ($giftCard->customer && $giftCard->customer->email) {
                $recipient = $giftCard->customer->email;
                $name = $giftCard->customer->name;
            }
        }

        if ($recipient && $name) {
            try {
                $this->setMailInfo($mailSetting);
                $generalSetting = GeneralSetting::latest()->first();
                Mail::to($recipient)->send(new GiftCardCreate($giftCard, $name, $generalSetting));
            } catch (Exception $e) {
                // Log error but don't fail the creation
                $this->logError("Failed to send gift card creation email: " . $e->getMessage());
            }
        }
    }

    /**
     * Update an existing gift card.
     *
     * @param GiftCard $giftCard Gift Card instance to update
     * @param array<string, mixed> $data Validated gift card data
     * @return GiftCard
     */
    public function updateGiftCard(GiftCard $giftCard, array $data): GiftCard
    {
        return $this->transaction(function () use ($giftCard, $data) {
            // Handle user/customer logic (clean API naming)
            if (isset($data['user']) && $data['user']) {
                $giftCard->user_id = $data['user_id'] ?? null;
                $giftCard->customer_id = null;
            } else {
                $giftCard->user_id = null;
                $giftCard->customer_id = $data['customer_id'] ?? null;
            }
            unset($data['user']); // Remove helper field

            // Update other fields
            if (isset($data['card_no'])) {
                $giftCard->card_no = $data['card_no'];
            }
            if (isset($data['amount'])) {
                $giftCard->amount = $data['amount'];
            }
            if (isset($data['expired_date'])) {
                $giftCard->expired_date = $data['expired_date'];
            }

            $giftCard->save();
            return $giftCard->fresh();
        });
    }

    /**
     * Bulk delete multiple gift cards (sets is_active = false).
     *
     * @param array<int> $ids Array of gift card IDs to delete
     * @return int Number of gift cards deleted
     */
    public function bulkDeleteGiftCards(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $giftCard = GiftCard::findOrFail($id);
                $this->deleteGiftCard($giftCard);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete gift card {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single gift card (sets is_active = false).
     *
     * @param GiftCard $giftCard Gift Card instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteGiftCard(GiftCard $giftCard): bool
    {
        return $this->transaction(function () use ($giftCard) {
            if ($giftCard->payments()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete gift card: gift card has associated payments');
            }

            // Set is_active = false instead of deleting
            $giftCard->is_active = false;
            $giftCard->save();
            return $giftCard->delete();
        });
    }

    /**
     * Recharge a gift card with additional amount.
     *
     * @param GiftCard $giftCard Gift Card instance to recharge
     * @param float $amount Amount to add to the gift card
     * @return GiftCard
     */
    public function rechargeGiftCard(GiftCard $giftCard, float $amount): GiftCard
    {
        return $this->transaction(function () use ($giftCard, $amount) {
            // Add amount to gift card
            $giftCard->amount += $amount;
            $giftCard->save();

            // Create recharge record
            GiftCardRecharge::create([
                'gift_card_id' => $giftCard->id,
                'amount' => $amount,
                'user_id' => Auth::id(),
            ]);

            // Send email notification
            $this->sendGiftCardRechargeEmail($giftCard, $amount);

            return $giftCard->fresh();
        });
    }

    /**
     * Send gift card recharge email notification.
     *
     * @param GiftCard $giftCard
     * @param float $rechargeAmount
     * @return void
     */
    private function sendGiftCardRechargeEmail(GiftCard $giftCard, float $rechargeAmount): void
    {
        $mailSetting = MailSetting::default()->first();
        if (!$mailSetting) {
            abort(Response::HTTP_BAD_REQUEST, 'Mail settings are not configured. Please contact the administrator.');
        }

        $recipient = null;
        $name = null;

        // Get recipient based on user_id or customer_id
        if ($giftCard->customer_id) {
            $giftCard->load('customer');
            if ($giftCard->customer && $giftCard->customer->email) {
                $recipient = $giftCard->customer->email;
                $name = $giftCard->customer->name;
            }
        } else {
            $giftCard->load('user');
            if ($giftCard->user && $giftCard->user->email) {
                $recipient = $giftCard->user->email;
                $name = $giftCard->user->name;
            }
        }

        if ($recipient && $name) {
            try {
                $this->setMailInfo($mailSetting);
                $generalSetting = GeneralSetting::latest()->first();
                Mail::to($recipient)->send(new GiftCardRechargeMail($giftCard, $name, $rechargeAmount, $generalSetting));
            } catch (Exception $e) {
                // Log error but don't fail the recharge
                $this->logError("Failed to send gift card recharge email: " . $e->getMessage());
            }
        }
    }
}

