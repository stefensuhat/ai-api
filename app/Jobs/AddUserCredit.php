<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\UserCredit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUserCredit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    public int $credit;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $credit)
    {
        $this->user = $user;
        $this->credit = $credit;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->user->token) {
            $userToken = new UserCredit;
            $userToken->user()->associate($this->user);
            $userToken->amount = $this->credit;
            $userToken->save();
        } else {
            $userToken = UserCredit::where('user_id', $this->user->id)->first();
            $userToken->amount += $this->credit;
            $userToken->save();
        }
    }
}
