<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQQuestion extends Model
{
    use HasFactory;
    protected $table = 'cms_faq_questions';

	public static function fetchquestions() {
		return FAQQuestion::where('status', '!=', 'delete')->get();
	}

	public static function saveQuestions($count, $req) {

		for ($i = 1; $i <= $count; $i++) {
			$exists = $req["faqid_" . $i];
			$faq = FAQQuestion::find($exists);
			if (isset($faq->id)) {
				$ques = "question_" . $i;
				$ans = "answer_" . $i;
				$faq->question = $req[$ques];
				$faq->answer = $req[$ans];
				$faq->status = trim($req["faqstatus_" . $i]);
			} else {
				$faq = new FAQQuestion;

				$ques = "question_" . $i;
				$ans = "answer_" . $i;
				$faq->question = $req[$ques];
				$faq->answer = $req[$ans];
				$faq->status = trim($req["faqstatus_" . $i]);
			}
			$faq->save();
		}
	}
}
