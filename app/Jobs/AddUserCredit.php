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
        $userCredit = UserCredit::where('user_id', $this->user->id)->first();
        if (! $userCredit) {
            $userCredit = new UserCredit;
            $userCredit->user()->associate($this->user);
        }
        $userCredit->amount += $this->credit;
        $userCredit->save();

    }
}
