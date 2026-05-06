<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public $filePath;
    public $application; 
    /**
     * コンストラクタでファイルのフルパスを受け取る
     */
    public function __construct($application,$filePath)
    {
        $this->filePath = $filePath;
        $this->application = $application;
    }

    /**
     * メールの設定（件名など）
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【重要】名前の詩のご注文を受付ました',
        );
    }

    /**
     * メールの内容（ビューの指定）
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pdf', // resources/views/emails/test.blade.php
        );
    }

    /**
     * 添付ファイルの設定
     */
    public function attachments(): array
    {
    // 名前を連結してファイル名を作成（拡張子 .pdf を忘れずに！）
    $fileName = '名前の詩_' . $this->application->last_name . $this->application->first_name . '様_申込書.pdf';

    return [
            Attachment::fromPath($this->filePath)
                ->as($fileName)
                ->withMime('application/pdf'),
        ];
    }
}