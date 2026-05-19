<?php

namespace App\Notifications;

use App\Models\Promotion;

class NewPromotionNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly Promotion $promotion,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $endDate = $this->promotion->end_date
            ? ' Ends ' . $this->formatDate($this->promotion->end_date) . '.'
            : '';

        return $this->buildPayload([
            'type'          => 'new_promotion',
            'title'         => 'New Promotion Available',
            'message'       => $this->promotion->title . ' — Check out our latest offer!' . $endDate,
            'entity_type'   => 'promotion',
            'entity_id'     => $this->promotion->id,
            'target_screen' => 'Home',
            'target_params' => [],
            'status'        => null,
        ]);
    }
}
