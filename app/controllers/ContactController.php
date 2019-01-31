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
                ->nest('heading', 'headings.resources')
                ->nest('content', 'contact', $content_data);
    }

    public function SubmitContactForm() {
        $template_data = array(
            'title' => 'Contact',
            'body_id' => 'chapter-index',
            'body_css' => 'scriptures section-heading'
        );

        $content_data = [];

        $input_data = Input::all();

        $rules = array (
            'full_name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'body' => 'required|min:5'
        );

        $validator = Validator::make ($input_data, $rules);

        if ($validator->passes()){
            Mail::send('emails.feedback', $input_data, function($message) use ($input_data)
            {
                $message->from($input_data['email'], $input_data['full_name']);
                $message->to(Config::get('contact_email'))->subject($input_data['subject']);
                $message->setBody($input_data['body']);
            });
            // Redirect to page
            /*return Redirect::route('contact')
                ->with('message', 'Your message has been sent. Thank You!');*/
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
                ->nest('heading', 'headings.resources')
                ->nest('content', 'contact', $content_data);

        }
    }
}