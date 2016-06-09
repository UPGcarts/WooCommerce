<?php

class WC_Gateway_Upg_Payment_Log
{
    public static function logError($message)
    {
        $logPath = wc_get_log_file_path('payco_wc_error');
        $message = '['.current_time('mysql').'] '.$message."\n";
        file_put_contents($logPath, $message, FILE_APPEND);
    }
}