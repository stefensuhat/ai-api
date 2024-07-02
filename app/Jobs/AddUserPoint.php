<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUserPoint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    public int $point;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $point)
    {
        $this->user = $user;
        $this->point = $point;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->user->token) {
            $userToken = new UserToken();
            $userToken->user()->associate($this->user);
            $userToken->amount = $this->point;
            $userToken->save();
        } else {
            $userToken = UserToken::where('user_id', $this->user->id)->first();
            $userToken->amount += $this->point;
            $userToken->save();
        }
    }
}
