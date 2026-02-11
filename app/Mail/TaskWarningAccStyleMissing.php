<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskWarningAccStyleMissing extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $task)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ALERT] Task Warning for Missing Account Style',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $tasks=json_decode($this->task);
        $accstyles=array();
        foreach ($tasks as $key => $task) {
            $accstyles[]=['acc_style_caption'=>$task->acc_style_caption];
        }
        $accstyles= json_decode(json_encode($accstyles), FALSE); //comvert to object
        return new Content(
            view: 'emails.email_task_accstyle_missing',
            with: [
                'org_name' => $this->task[0]->org_name,
                'task_name' => $this->task[0]->task_name,
                'task_time' =>$this->task[0]->task_maker_time,
                'task_msg' =>$this->task[0]->task_last_message,
                'accstyles' =>$accstyles,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
