<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $viewName;
    public $viewData;

    public function __construct(string $subjectLine, string $viewName, array $viewData)
    {
        $this->subjectLine = $subjectLine;
        $this->viewName = $viewName;
        $this->viewData = $viewData;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->view($this->viewName, $this->viewData);
    }
}