<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PttAudioSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $audioUrl;
    public $senderId;
    public $senderName;

    public function __construct($audioUrl, $senderId, $senderName)
    {
        $this->audioUrl = $audioUrl;
        $this->senderId = $senderId;
        $this->senderName = $senderName;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('ptt'),
        ];
    }

    public function broadcastAs()
    {
        return 'PttAudioSent';
    }
}
