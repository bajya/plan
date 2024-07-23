<?php
namespace App\Library;

use App\BusRuleRef;
use App\Notification;
use App\User;
use FCM;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use Mail;

class Notify {

// 	public static function sendMail($view, $data, $subject) {
// 		$sender = Notify::getBusRuleRef('sender_id');
// 		$mail = Mail::send($view, $data, function ($message) use ($data, $sender, $subject) {
// 			$message->to($data['email'], $data['name'])->subject($subject);
// 			$message->from($sender, 'Laravel');
// 		});
// 		return $mail;
// 	}

	public static function sendMail($view, $data, $subject) {
		$sender = env('CANCEL_MAIL');
		$mail = Mail::send($view, $data, function ($message) use ($data, $sender, $subject) {
			$message->to($sender, 'Laravel')->subject($subject);
			$message->from($sender, $data['name']);
		});
		return $mail;
	}
}