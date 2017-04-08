<?php namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountActivation extends Mailable {

    use Queueable, SerializesModels;

    protected $link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($link) {
        $this->link = $link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->subject('Activate your TripCalc account')
                    ->view('emails.account.activation')
                    ->with(['link' => $this->link]);
    }

    public function link() {
        return $this->link;
    }
}
