<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;


class MailSendController extends Controller
{
    public function index() {
        $data = [];
        Mail::send('emails.test',$data,function($message) {
            $message->to('test1@example.com', 'Test')
                    ->subject('this is a test mail');
        });
    }
}
