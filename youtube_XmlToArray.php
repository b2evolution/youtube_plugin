<?php

/**
 * Author   : MA Razzaque Rupom (rupom_315@yahoo.com, rupom.bd@gmail.com)
 * Version  : 1.0
 * Date     : 02 March, 2006
 * Purpose  : Creating Hierarchical Array from XML Data
 * Released : Under GPL
 */

class youtube_XmlToArray extends youtube_plugin
{

    var $xml='';

    /**
     * Default Constructor
     * @param $xml = xml data
     * @return none
     */

    function __construct( $xml_url )
    {
        $this->fetch_remote_page($xml_url);
    }


    /**
     * Fetch remote page
     * Attempt to retrieve a remote page, first with cURL, then fopen, then fsockopen
     * @param $url
     * @return $data = The remote page as a string
     */

    function fetch_remote_page( $url ) {
        $data = '';
        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec ($ch);
            curl_close ($ch);
        } elseif ( ini_get('allow_url_fopen') ) {
            // cURL not supported, try fopen
            $hf = fopen($url, 'r');
            for ($data =''; $buf=fread($hf,1024); ) {  //read the complete file (binary safe)
                $data .= $buf;
            }
            fclose($hf);
        } else {
            // As a last resort, try fsockopen
            $url_parsed = parse_url($url);
            if ( empty($url_parsed['scheme']) ) {
                $url_parsed = parse_url('http://'.$url);
            }

            $port = $url_parsed["port"];
            if ( !$port ) {
                $port = 80;
            }

            $path = $url_parsed["path"];
            if ( empty($path) ) {
                $path="/";
            }
            if ( !empty($url_parsed["query"]) ) {
                $path .= "?".$url_parsed["query"];
            }

            $host = $url_parsed["host"];
            $foundBody = false;

            $out = "GET $path HTTP/1.0\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n\r\n";

            if ( !$fp = @fsockopen($host, $port, $errno, $errstr, 30) ) {
                $error = $errno;
                $error .= $errstr;
                return $error;
            }
            fwrite($fp, $out);
            while (!feof($fp)) {
                $s = fgets($fp, 128);
                if ( $s == "\r\n" ) {
                    $foundBody = true;
                    continue;
                }
                if ( $foundBody ) {
                    $body .= $s;
                }
            }
            fclose($fp);

            $data = trim($body);
        }
        $this->xml = $data;
    }


    /**
     * _struct_to_array($values, &$i)
     *
     * This is adds the contents of the return xml into the array for easier processing.
     * Recursive, Static
     *
     * @access    private
     * @param    array  $values this is the xml data in an array
     * @param    int    $i  this is the current location in the array
     * @return    Array
     */

    function _struct_to_array($values, &$i)
    {
        $child = array();
        if (isset($values[$i]['value'])) array_push($child, $values[$i]['value']);

        while ($i++ < count($values)) {
            switch ($values[$i]['type']) {
                case 'cdata':
                    array_push($child, $values[$i]['value']);
                    break;

                case 'complete':
                    $name = $values[$i]['tag'];
                    if(!empty($name)){
                        $child[$name]= ( isset($values[$i]['value']) ? $values[$i]['value'] : '' );
                        if(isset($values[$i]['attributes'])) {
                            $child[$name] = $values[$i]['attributes'];
                        }
                    }
                    break;

                case 'open':
                    $name = $values[$i]['tag'];
                    $size = isset($child[$name]) ? sizeof($child[$name]) : 0;
                    $child[$name][$size] = $this->_struct_to_array($values, $i);
                    break;

                case 'close':
                    return $child;
                    break;
            }
        }
        return $child;
    }//_struct_to_array

    /**
     * createArray($data)
     *
     * This is adds the contents of the return xml into the array for easier processing.
     *
     * @access    public
     * @param    string    $data this is the string of the xml data
     * @return    Array
     */
    function createArray()
    {
        $xml    = $this->xml;
        $values = array();
        $index  = array();
        $array  = array();
        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parse_into_struct($parser, $xml, $values, $index);
        xml_parser_free($parser);
        $i = 0;
        $name = $values[$i]['tag'];
        $array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
        $array[$name] = $this->_struct_to_array($values, $i);
        return $array;
    }//createArray


}//XmlToArray