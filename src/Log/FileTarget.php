<?php
namespace JSwoole\Log;

class FileTarget implements Target
{
    public $file='';

    public function __construct($file)
    {
        $this->file=$file;
    }

    public function export(array $message)
    {
        if (!($message[2] instanceof string)) {
            $message[2]=json_encode($message[2], JSON_UNESCAPED_UNICODE);
        }
        $content=Date('Y-m-d H:i:s', $message[0]).' '.$message[1].' '.$message[2]."\n";
        file_put_contents($this->file, $content, FILE_APPEND);
    }
}