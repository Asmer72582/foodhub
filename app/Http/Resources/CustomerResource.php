<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        // Safely get unread notifications count
        $unreadNotificationsCount = 0;
        try {
            $unreadNotificationsCount = $this->unreadNotifications()->count();
        } catch (\Exception $e) {
            // Silently handle any notification-related errors
            \Log::error('Error getting notifications count: ' . $e->getMessage());
        }

        return [
            "id"           => $this->id,
            "name"         => $this->name,
            "username"     => $this->username,
            "email"        => $this->email,
            "branch_id"    => $this->branch_id,
            "phone"        => $this->phone === null ? '' : $this->phone,
            "status"       => $this->status,
            "image"        => $this->getFirstMediaUrl('profile') ?: asset('images/default/profile.png'),
            "country_code" => $this->country_code,
            "messages"     => $unreadNotificationsCount,
            "address"      => $this->address,
        ];
    }
}
