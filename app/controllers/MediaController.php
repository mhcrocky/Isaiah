<?php

class MediaController extends BaseController {

    public function showMediaCommentary($filename)
    {
        $media = new MediaRepository();
        $media->ReadChapterCommentary($filename);
    }
}