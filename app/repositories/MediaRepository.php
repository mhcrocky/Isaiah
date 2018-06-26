<?php

class MediaRepository {
    public function ReadChapterCommentary($filename){
        $location = public_path() . "/mp3/commentary/${filename}";

        $extension = substr(strrchr($filename,'.'),1);
        if ($extension == "mp3") {
            $mimeType = "audio/mpeg";
            //$mimeType = "audio/mpeg, audio/x-mpeg, audio/x-mpeg-3, audio/mpeg3";
        } else if ($extension == "ogg") {
            $mimeType = "audio/ogg";
            //$mimeType = "audio/ogg, audio/x-ogg, application/ogg, application/x-ogg";
        }

        $this->_smartReadFile($location, $filename, $mimeType);
    }

    /**
     * Reads the requested portion of a file and sends its contents to the client with the appropriate headers.
     *
     * This HTTP_RANGE compatible read file function is necessary for allowing streaming media to be skipped around in.
     *
     * @param string $location
     * @param string $filename
     * @param string $mimeType
     * @return void
     *
     * @link https://groups.google.com/d/msg/jplayer/nSM2UmnSKKA/Hu76jDZS4xcJ
     * @link http://php.net/manual/en/function.readfile.php#86244
     */
    private function _smartReadFile($location, $filename, $mimeType = 'application/octet-stream')
    {
        if (!file_exists($location))
        {
            ob_end_clean();
            header ("HTTP/1.1 404 Not Found");
            header("Location: $location");
            exit;
        }

        $size	= filesize($location);
        $time	= date('r', filemtime($location));

        $fm		= @fopen($location, 'rb');
        if (!$fm)
        {
            ob_end_clean();
            header ("HTTP/1.1 505 Internal server error");
            header("Location: $location");
            exit;
        }

        $begin	= 0;
        $end	= $size - 1;

        if (isset($_SERVER['HTTP_RANGE']))
        {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches))
            {
                $begin	= intval($matches[1]);
                if (!empty($matches[2]))
                {
                    $end	= intval($matches[2]);
                }
            }
        }

        //ob_end_clean();

        if (isset($_SERVER['HTTP_RANGE']))
        {
            header('HTTP/1.1 206 Partial Content');
        }
        else
        {
            header('HTTP/1.1 200 OK');
        }

        header("Content-Type: application/octet-stream");
        header("Content-Type: $mimeType");
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length:' . (($end - $begin) + 1));
        if (isset($_SERVER['HTTP_RANGE']))
        {
            header("Content-Range: bytes $begin-$end/$size");
        }
        header("Content-Disposition: inline; filename=$filename");
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: $time");

        $cur	= $begin;
        fseek($fm, $begin, 0);

        while(!feof($fm) && $cur <= $end && (connection_status() == 0))
        {
            print fread($fm, min(1024 * 16, ($end - $cur) + 1));
            $cur += 1024 * 16;
            //flush();
        }

        exit;
    }
}