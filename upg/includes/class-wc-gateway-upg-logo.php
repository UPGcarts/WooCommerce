<?php

class WC_Gateway_Upg_Payment_Logo
{
    const LOGO_SUB_DIR = 'logo/';
    private $pluginPath;
    private $pluginUrl;

    public function __construct($pluginPath)
    {
        $this->pluginPath = plugin_dir_path($pluginPath).self::LOGO_SUB_DIR;
        $this->pluginUrl = plugin_dir_url($pluginPath);
    }

    private function checkIcon()
    {
        if($this->pluginPath) {
            foreach(glob($this->pluginPath."/*.{jpg,png,gif}", GLOB_BRACE) as $image) {
                if(file_exists($image)) {
                    return pathinfo($image, PATHINFO_BASENAME);
                }
            }
        }

        return '';
    }

    public function getUrlForLogo()
    {
        $logo = $this->checkIcon();
        if(!empty($logo)) {
            return $this->pluginUrl . self::LOGO_SUB_DIR . $logo;
        }

        return '';
    }
}