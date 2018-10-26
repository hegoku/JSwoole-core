<?php
namespace JSwoole\Log;

interface Target
{
    public function export(array $message);
}