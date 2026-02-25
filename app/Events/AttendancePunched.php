<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class AttendancePunched
 *
 * Broadcasts real-time attendance updates via Laravel Reverb when a physical device pushes a punch.
 */
class AttendancePunched implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Attendance $attendance
     * @param string $punchType 'Check-in' or 'Check-out'
     */
    public function __construct(
        public Attendance $attendance,
        public string $punchType
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Broadasting to a secure channel for admins/HR
            new PrivateChannel('attendance.live'),
        ];
    }

    /**
     * Define the broadcast payload.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->attendance->id,
            'employee_name' => $this->attendance->employee->name ?? 'Unknown',
            'time' => $this->punchType === 'Check-in' ? $this->attendance->checkin : $this->attendance->checkout,
            'type' => $this->punchType,
            'status' => $this->attendance->status->value,
        ];
    }
}
