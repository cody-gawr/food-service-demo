<?php

namespace App\Listeners\Auth;

use App\Repositories\PersonalVerificationCodeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use App\Events\Auth\PasswordForgot;
use App\Mail\Auth\SecretCode;
use Illuminate\Support\Carbon;

class SendSecretCode implements ShouldQueue
{
    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     * Create the event listener.
     * @param \App\Repositories\PersonalVerificationCodeRepository $personalVerificationCodeRepository
     */
    public function __construct(
        public readonly PersonalVerificationCodeRepository $personalVerificationCodeRepository
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PasswordForgot $event): void
    {
        $code = $this->personalVerificationCodeRepository->generate6DigitsCode();
        $event->user->personalVerificationCodes()->create([
            'expires_at' => Carbon::now()->addDay()->toDateTimeString(),
            'user_uuid' => $event->user->uuid,
            'code' => $code
        ]);
        Mail::to($event->user)->send(new SecretCode($code));
    }
}
