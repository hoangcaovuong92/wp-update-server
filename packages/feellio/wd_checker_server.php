<?php 
if (!class_exists('wd_checker_server')) {
    class wd_checker_server extends Wpup_UpdateServer {
        protected function filterMetadata($meta, $request) {
            $meta = parent::filterMetadata($meta, $request);
            //Include license information in the update metadata. This saves an HTTP request
            //or two since the plugin doesn't need to explicitly fetch license details.
            $type               = !empty($request->query['type']) ? $request->query['type'] : '';
            $wpdance_query      = !empty($request->query['wd_data']) ? $this->decode_data($request->query['wd_data']) : '';
            $purchase_code      = !empty($wpdance_query['purchase_code']) ? $wpdance_query['purchase_code'] : '';
            $url                = !empty($wpdance_query['url']) ? $wpdance_query['url'] : '';
            $server_url         = !empty($wpdance_query['server_url']) ? $wpdance_query['server_url'] : '';
            $theme_slug         = !empty($wpdance_query['theme_slug']) ? $wpdance_query['theme_slug'] : '';

            $theme_changelog_url    = $server_url.'changelog/'.$theme_slug.'/theme_changelog.html';
            $plugin_desc_url        = $server_url.'changelog/'.$theme_slug.'/plugin_desc.html';
            $plugin_install_url     = $server_url.'changelog/'.$theme_slug.'/plugin_install.html';
            $plugin_changelog_url   = $server_url.'changelog/'.$theme_slug.'/plugin_changelog.html';

            if ($type == 'plugin') {
                $meta['sections']['description']     = file_get_contents( $plugin_desc_url );
                $meta['sections']['installation']    = file_get_contents( $plugin_install_url );
                $meta['sections']['changelog']       = file_get_contents( $plugin_changelog_url );
                $meta['rating']             = 100;
                $meta['num_ratings']        = 49;
                $meta['downloaded']         = 152;
                $meta['active_installs']    = 152;
            }elseif ($type == 'theme'){
                $meta['details_url']    =  $theme_changelog_url;
            }
            //Only include the download URL if the license is valid.
            if ( $purchase_code && $this->checkIsValid($purchase_code) ) {
                //Append the license key or to the download URL.
                $args = array( 'wd_data' => $request->query['wd_data'] );
                $meta['download_url']   = self::addQueryArg($args, $meta['download_url']);
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
            $wpdance_query   = !empty($request->query['wd_data']) ? $this->decode_data($request->query['wd_data']) : '';
            $purchase_code   = !empty($wpdance_query['purchase_code']) ? $wpdance_query['purchase_code'] : '';
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

        public function checkIsValid($purchase_code = ''){
            $error = 0;
            // if (!($this->check_purchase_code($purchase_code)) || empty($purchase_code)) { //if wrong purchase code
            //     $error++;
            // }
            if (empty($purchase_code)) { //if wrong purchase code
                $error++;
            }
            return $error == 0 ? true : false;
        }

        /********** Check purchase code exist **********/
        public function get_purchase_code_info($purchase_code = ''){
            if (!$purchase_code) return false;
            $username = 'tvlgiao';
            // Set API Key  
            $api_key = '7qplew3cz4di546ozrs8dyzyzbpjruza';
            
            // Open cURL channel
            $ch = curl_init();
             
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/". $username ."/". $api_key ."/verify-purchase:". $purchase_code .".json");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            //Set the user agent
            $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);    

            // Decode returned JSON
            $output = json_decode(curl_exec($ch), true);
            // Close Channel
            curl_close($ch);
            // Return output
            //printf(json_encode($output));
            return $output;
        }

        public function check_purchase_code($purchase_code = ''){
            if (!$purchase_code) return false;
            $output = $this->get_purchase_code_info($purchase_code);
            // Return output
            return (!empty($output['verify-purchase']['buyer'])) ? true : false ;
        }
    }
} ?>