<?php

class ContactController extends \BaseController {
    public function GetContactForm() {
        $template_data = array(
            'title' => 'Contact',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );
        $content_data = [];

        return View::make('layouts.master', $template_data)
                ->nest('tracking_code', 'widgets.tracking-code')
                ->nest('heading', 'headings.resources')
                ->nest('content', 'contact', $content_data);
    }

    public function SubmitContactForm() {
        $template_data = array(
            'title' => 'Contact',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        //$content_data = [];

        $input_data = Input::all();

        $content_data = ['input_data' => $input_data];

        $rules = array (
            'full_name' => 'required',
            'email' => 'required|email',
            //'subject' => 'required',
            'phone_number' => array('Regex:/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9])\s*\)|([2-9]1[02-9]|[2-9][02-8]1|[2-9][02-8][02-9]))\s*(?:[.-]\s*)?)?([2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/'),
            'body' => 'required|min:5'
        );

        $validator = Validator::make ($input_data, $rules);

        if ($validator->passes()){
            Mail::send('emails.feedback', $input_data, function($message) use ($input_data)
            {
                //$message->from(Config::get('app.system_email'), $input_data['full_name']);
                $message->from($input_data['email'], $input_data['full_name']);
                $message->to(Config::get('app.contact_email'))->subject(Config::get('app.contact_subject'));
                $message->setBody($input_data['body']);
            });
            // Redirect to page
            /*return Redirect::route('contact')
                ->with('message', 'Your message has been sent. Thank You!');*/
            $content_data = [];
            $content_data['message'] = 'Your message has been sent. Thank You!';
            return View::make('layouts.master', $template_data)
                ->nest('heading', 'headings.resources')
                ->nest('content', 'contact', $content_data);
            //return View::make('contact');
        }else{
            //return contact form with errors
            /*return Redirect::route('contact')
                ->with('error', 'Feedback must contain more than 5 characters. Try Again.');*/
            $content_data['errors'] = $validator->messages();
            return View::make('layouts.master', $template_data)
                ->nest('tracking_code', 'widgets.tracking-code')
                ->nest('heading', 'headings.resources')
                ->nest('content', 'contact', $content_data);
        }
    }
}