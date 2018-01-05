<?php 
if (!class_exists('WD_Checker_Server')) {
    class WD_Checker_Server extends Wpup_UpdateServer {
        public $theme_slug  = 'thefuture';
        public $server_url  = 'http://192.168.1.96/WordPress_TheFuture/wordpress-update/changelog/';
       
        protected function filterMetadata($meta, $request) {
            $meta = parent::filterMetadata($meta, $request);
            //Include license information in the update metadata. This saves an HTTP request
            //or two since the plugin doesn't need to explicitly fetch license details.
            $type               = !empty($request->query['type']) ? $request->query['type'] : '';
            $license            = !empty($request->query['license']) ? $this->decode_data($request->query['license']) : '';
            $purchase_code      = !empty($license['purchase_code']) ? $license['purchase_code'] : '';
            $url                = !empty($license['url']) ? $license['url'] : '';

            $theme_changelog_url    = $this->server_url.$this->theme_slug.'/theme_changelog.html';
            $plugin_desc_url        = $this->server_url.$this->theme_slug.'/plugin_desc.html';
            $plugin_install_url     = $this->server_url.$this->theme_slug.'/plugin_install.html';
            $plugin_changelog_url   = $this->server_url.$this->theme_slug.'/plugin_changelog.html';

            //Only include the download URL if the license is valid.
            if ( $purchase_code && $this->checkIsValid($purchase_code) ) {
                //Append the license key or to the download URL.
                $args = array( 'license' => $request->query['license'] );
                $meta['download_url']   = self::addQueryArg($args, $meta['download_url']);
                if ($type == 'plugin') {
                    if (file_get_contents( $plugin_desc_url )) {
                         $meta['sections']['description']     = file_get_contents( $plugin_desc_url );
                    }
                    if (file_get_contents( $plugin_install_url )) {
                        $meta['sections']['installation']    = file_get_contents( $plugin_install_url );
                    }
                    if (file_get_contents( $plugin_changelog_url )) {
                        $meta['sections']['changelog']       = file_get_contents( $plugin_changelog_url );
                    }
                    $meta['rating']             = 100;
                    $meta['num_ratings']        = 49;
                    $meta['downloaded']         = 152;
                    $meta['active_installs']    = 152;
                }elseif ($type == 'theme'){
                    $meta['details_url']    =  $theme_changelog_url;
                }
                
            }else{
                unset($meta['download_url']);
            }
            return $meta;
        } 


        protected function decode_data($data){
            return unserialize(base64_decode($data));
        }    


        protected function checkAuthorization($request) {
            parent::checkAuthorization($request);
            //Prevent download if the user doesn't have a valid license.
            $license         = !empty($request->query['license']) ? $this->decode_data($request->query['license']) : '';
            $purchase_code   = !empty($license['purchase_code']) ? $license['purchase_code'] : '';
            $url      = !empty($license['url']) ? $license['url'] : '';
            if ( $request->action === 'download' ) {
                if ( !isset($purchase_code) ) {
                    $message = 'You must provide a license key to download this plugin.';
                    $this->exitWithError($message, 403);
                } elseif (!$this->checkIsValid($purchase_code)) {
                    $message = 'Sorry, your license is not valid.';
                    $this->exitWithError($message, 403);
                }
            }
        }

        public function checkIsValid($code_to_verify = ''){
            if ($code_to_verify) {
                $username = 'tvlgiao';
                // Set API Key  
                $api_key = '7qplew3cz4di546ozrs8dyzyzbpjruza';
                
                // Open cURL channel
                $ch = curl_init();
                 
                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/". $username ."/". $api_key ."/verify-purchase:". $code_to_verify .".json");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                //Set the user agent
                $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
                curl_setopt($ch, CURLOPT_USERAGENT, $agent);    

                // Decode returned JSON
                $output = json_decode(curl_exec($ch), true);
                // Close Channel
                curl_close($ch);
                // Return output
                return !empty($output['verify-purchase']['buyer']) ? true : false;
            }
        }
    }
} ?>