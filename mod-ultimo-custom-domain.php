<?php
/**
  *  Ultimo Custom Domain Customizations
  */

// Add option to $ccl_options
ccl_create_option('ccl_enable_ultimo_domain_customizations', 'off', 'Ultimo Custom Domain Customizations');

/**
  *  Ultimo Custom Domain Customizations
  */
if ( get_site_option('ccl_enable_ultimo_domain_customizations', 'off') == "on" ) {

	if (!defined('CUSTOM_DOMAIN_EMAIL')) {
		define('CUSTOM_DOMAIN_EMAIL', 'info@bizlaunchuniversity.com');
	}

	/* CIDR */

	/**
	 * CIDR.php
	 *
	 * Utility Functions for IPv4 ip addresses. 
	 * Supports PHP 5.3+ (32 & 64 bit)
	 * @author Jonavon Wilcox <jowilcox@vt.edu>
	 * @revision Carlos Guimarães <cvsguimaraes@gmail.com>
	 * @version Wed Mar  12 13:00:00 EDT 2014
	 */
	
	/**
	 * class CIDR.
	* Holds static functions for ip address manipulation.
	*/
	class CIDR {
		/**
		 * method CIDRtoMask
		 * Return a netmask string if given an integer between 0 and 32. I am 
		 * not sure how this works on 64 bit machines.
		 * Usage:
		 *     CIDR::CIDRtoMask(22);
		 * Result:
		 *     string(13) "255.255.252.0"
		 * @param $int int Between 0 and 32.
		 * @access public
		 * @static
		 * @return String Netmask ip address
		 */
		public static function CIDRtoMask($int) {
			return long2ip(-1 << (32 - (int)$int));
		}

		/**
		 * method validIP.
		 * Determine if a given input is a valid IPv4 address.
		 * Usage:
		 *     CIDR::validIP('0.50.45.50');
		 * Result:
		 *     bool(false)
		 * @param $ipinput String a IPv4 formatted ip address.
		 * @access public
		 * @static
		 * @return bool True if the input is valid.
		 */
		public static function validIP($ipinput)
		{
			return filter_var($ipinput, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
		}
		
		/**
		 * method countSetBits.
		 * Return the number of bits that are set in an integer.
		 * Usage:
		 *     CIDR::countSetBits(ip2long('255.255.252.0'));
		 * Result:
		 *     int(22)
		 * @param $int int a number
		 * @access public
		 * @static
		 * @see http://stackoverflow.com/questions/109023/best-algorithm-to-co\
		 * unt-the-number-of-set-bits-in-a-32-bit-integer
		 * @return int number of bits set.
		 */
		public static function countSetbits($int){
			$int = $int & 0xFFFFFFFF;
			$int = ( $int & 0x55555555 ) + ( ( $int >> 1 ) & 0x55555555 ); 
			$int = ( $int & 0x33333333 ) + ( ( $int >> 2 ) & 0x33333333 );
			$int = ( $int & 0x0F0F0F0F ) + ( ( $int >> 4 ) & 0x0F0F0F0F );
			$int = ( $int & 0x00FF00FF ) + ( ( $int >> 8 ) & 0x00FF00FF );
			$int = ( $int & 0x0000FFFF ) + ( ( $int >>16 ) & 0x0000FFFF );
			$int = $int & 0x0000003F;
			return $int;
		}
		
		/**
		 * method validNetMask.
		 * Determine if a string is a valid netmask.
		 * Usage:
		 *     CIDR::validNetMask('255.255.252.0');
		 *     CIDR::validNetMask('127.0.0.1');
		 * Result:
		 *     bool(true)
		 *     bool(false)
		 * @param $netmask String a 1pv4 formatted ip address.
		 * @see http://www.actionsnip.com/snippets/tomo_atlacatl/calculate-if-\
		 * a-netmask-is-valid--as2-
		 * @access public
		 * @static
		 * return bool True if a valid netmask.
		 */
		public static function validNetMask($netmask){
			$netmask = ip2long($netmask);
			if($netmask === false) return false;
			$neg = ((~(int)$netmask) & 0xFFFFFFFF);
			return (($neg + 1) & $neg) === 0;
		}

		/**
		 * method maskToCIDR.
		 * Return a CIDR block number when given a valid netmask.
		 * Usage:
		 *     CIDR::maskToCIDR('255.255.252.0');
		 * Result:
		 *     int(22)
		 * @param $netmask String a 1pv4 formatted ip address.
		 * @access public
		 * @static
		 * @return int CIDR number.
		 */
		public static function maskToCIDR($netmask){
			if(self::validNetMask($netmask)){
				return self::countSetBits(ip2long($netmask));
			}
			else {
				throw new Exception('Invalid Netmask');
			}
		}

		/**
		 * method alignedCIDR.
		 * It takes an ip address and a netmask and returns a valid CIDR
		 * block.
		 * Usage:
		 *     CIDR::alignedCIDR('127.0.0.1','255.255.252.0');
		 * Result:
		 *     string(12) "127.0.0.0/22"
		 * @param $ipinput String a IPv4 formatted ip address.
		 * @param $netmask String a 1pv4 formatted ip address.
		 * @access public
		 * @static
		 * @return String CIDR block.
		 */
		public static function alignedCIDR($ipinput,$netmask){
			$alignedIP = long2ip((ip2long($ipinput)) & (ip2long($netmask)));
			return "$alignedIP/" . self::maskToCIDR($netmask);
		}

		/**
		 * method IPisWithinCIDR.
		 * Check whether an IP is within a CIDR block.
		 * Usage:
		 *     CIDR::IPisWithinCIDR('127.0.0.33','127.0.0.1/24');
		 *     CIDR::IPisWithinCIDR('127.0.0.33','127.0.0.1/27');
		 * Result: 
		 *     bool(true)
		 *     bool(false)
		 * @param $ipinput String a IPv4 formatted ip address.
		 * @param $cidr String a IPv4 formatted CIDR block. Block is aligned
		 * during execution.
		 * @access public
		 * @static
		 * @return String CIDR block.
		 */
		public static function IPisWithinCIDR($ipinput,$cidr){
			$cidr = explode('/',$cidr);
			$cidr = self::alignedCIDR($cidr[0],self::CIDRtoMask((int)$cidr[1]));
			$cidr = explode('/',$cidr);
			$ipinput = (ip2long($ipinput));
			$ip1 = (ip2long($cidr[0]));
			$ip2 = ($ip1 + pow(2, (32 - (int)$cidr[1])) - 1);
			return (($ip1 <= $ipinput) && ($ipinput <= $ip2));
		}

		/**
		 * method maxBlock.
		 * Determines the largest CIDR block that an IP address will fit into.
		 * Used to develop a list of CIDR blocks.
		 * Usage:
		 *     CIDR::maxBlock("127.0.0.1");
		 *     CIDR::maxBlock("127.0.0.0");
		 * Result:
		 *     int(32)
		 *     int(8)
		 * @param $ipinput String a IPv4 formatted ip address.
		 * @access public
		 * @static
		 * @return int CIDR number.
		 */
		public static function maxBlock($ipinput) {
			return self::maskToCIDR(long2ip(-(ip2long($ipinput) & -(ip2long($ipinput)))));
		}
		
		/**
		 * method rangeToCIDRList.
		 * Returns an array of CIDR blocks that fit into a specified range of
		 * ip addresses.
		 * Usage:
		 *     CIDR::rangeToCIDRList("127.0.0.1","127.0.0.34");
		 * Result:
		 *     array(7) { 
		 *       [0]=> string(12) "127.0.0.1/32"
		 *       [1]=> string(12) "127.0.0.2/31"
		 *       [2]=> string(12) "127.0.0.4/30"
		 *       [3]=> string(12) "127.0.0.8/29"
		 *       [4]=> string(13) "127.0.0.16/28"
		 *       [5]=> string(13) "127.0.0.32/31"
		 *       [6]=> string(13) "127.0.0.34/32"
		 *     }
		 * @param $startIPinput String a IPv4 formatted ip address.
		 * @param $startIPinput String a IPv4 formatted ip address.
		 * @see http://null.pp.ru/src/php/Netmask.phps
		 * @return Array CIDR blocks in a numbered array.
		 */
		public static function rangeToCIDRList($startIPinput,$endIPinput=NULL) {
			$start = ip2long($startIPinput);
			$end =(empty($endIPinput))?$start:ip2long($endIPinput);
			while($end >= $start) {
				$maxsize = self::maxBlock(long2ip($start));
				$maxdiff = 32 - intval(log($end - $start + 1)/log(2));
				$size = ($maxsize > $maxdiff)?$maxsize:$maxdiff;
				$listCIDRs[] = long2ip($start) . "/$size";
				$start += pow(2, (32 - $size));
			}
			return $listCIDRs;
		}

			/**
		 * method cidrToRange.
		 * Returns an array of only two IPv4 addresses that have the lowest ip
			 * address as the first entry. If you need to check to see if an IPv4
			 * address is within range please use the IPisWithinCIDR method above.
		 * Usage:
		 *     CIDR::cidrToRange("127.0.0.128/25");
		 * Result:
			 *     array(2) {
			 *       [0]=> string(11) "127.0.0.128"
			 *       [1]=> string(11) "127.0.0.255"
			 *     }
		 * @param $cidr string CIDR block
		 * @return Array low end of range then high end of range.
		 */
		public static function cidrToRange($cidr) {
			$range = array();
			$cidr = explode('/', $cidr);
			$range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
			$range[1] = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
			return $range;
		}
	}

	#wu_custom_domain_after
	function custom_domain_text () {
		$server_addr = $_SERVER['SERVER_ADDR'];
		# Note: In order to set a Custom Domain Name you must have purchased and pointed your custom domain name already. 
		# Do not use this for changing a sub domain name! To do that contact info@bizlaunchuniversity.com and request a sub domain name change.
		$custom_domain = wu_get_current_site()->get_meta('custom-domain');

		if ($custom_domain) {    
		$main_domain = dns_get_record($custom_domain, DNS_A + DNS_CNAME);
		$main_domain_type = $main_domain[0]['type'];
		$www_domain = dns_get_record("www.".$custom_domain, DNS_A + DNS_CNAME);
		$www_domain_type = $www_domain[0]['type'];
		# echo "<pre>".print_r($main_domain)."</pre>";

		echo "<div class=\"custom-domain-status\">Custom Domain Status</div>";
		echo "<div class=\"custom-domain-check\"><b>$custom_domain</b> ";
		if ($main_domain_type == 'CNAME') {
			echo "is type CNAME pointed to ".$main_domain[0]['target']."</div>";
			$main_domain_status = "true";
		} elseif ($main_domain_type == 'A' ) {
			echo "is type A pointed to ".$main_domain[0]['ip']."</div>";
			$main_domain_status = "true";
		} else {
			echo "<b>Failed to resolve domain</b></div>";
		}
		
		echo "<div class=\"custom-domain-check\"><b>www.$custom_domain</b> ";
		if ($www_domain_type == 'CNAME') {
			echo "is type CNAME pointed to ".$www_domain[0]['target']."</div>";
			$www_domain_status = "true";
		} elseif ($www_domain_type == 'A' ) {
			echo "is type A pointed to ".$www_domain[0]['ip']."</div>";
			$www_domain_status = "true";
		} else {
			echo "<b>Failed to resolve domain</b></div>";
		}

		# Cloudflare IP's
		$cloudflare_ips = array("172.67.178.95","104.21.91.193","172.67.153.32","172.67.143.6","104.21.87.115");
		$cloudflare_cidr = array(
			"173.245.48.0/20",
			"103.21.244.0/22",
			"103.22.200.0/22",
			"103.31.4.0/22",
			"141.101.64.0/18",
			"108.162.192.0/18",
			"190.93.240.0/20",
			"188.114.96.0/20",
			"197.234.240.0/22",
			"198.41.128.0/17",
			"162.158.0.0/15",
			"104.16.0.0/13",
			"104.24.0.0/14",
			"172.64.0.0/13",
			"131.0.72.0/22",
		);
				
		# Loop through CIDR ranges.      
		function CDIRLoop($ip,$cidr_ranges) {
			foreach($cidr_ranges as &$cidr) {
			if ( CIDR::IPisWithinCIDR($ip,$cidr) ) {
				return true;
			}
			}
		}
		
		# Check root domain is pointed and present success or error message.
		if ($main_domain[0]['ip'] == $server_addr) {
			echo "<div class=\"custom-domain-success\">SUCCESS: $custom_domain is pointed properly!</div>";
		} elseif (CDIRLoop($main_domain[0]['ip'],$cloudflare_cidr)) {
			echo "<div class=\"custom-domain-warning\">WARNING: $custom_domain is on Cloudflare, make sure you have $custom_domain pointed at $server_addr within Cloudflare</div>";
		} else {
			echo "<div class=\"custom-domain-error\">ERROR: $custom_domain is not pointed to $server_addr, please check your DNS!</div>";
		}

		if (in_array($www_domain[0]['ip'],$cloudflare_ips)) {
			echo "<div class=\"custom-domain-warning\">WARNING: www.$custom_domain is on Cloudflare, make sure you have www.$custom_domain pointed at $server_addr within Cloudflare</div>";
		} elseif ($www_domain[0]['ip'] == $server_addr) {
			echo "<div class=\"custom-domain-success\">SUCCESS: www.$custom_domain is pointed properly!</div>";
		} else {
			echo "<div class=\"custom-domain-error\">ERROR: www.$custom_domain is not pointed to $server_addr</div>";
		}
		
		echo "<br><br>";
		echo "<div class=\"custom-domain-status\">Support</div>";
		echo "<div class=\"custom-domain-text\">Note: In order to set a Custom Domain Name you must have purchased and pointed your custom domain name already.";
		echo "<br><br>Do not use this for changing a sub domain name! To do that contact <a href=\"malto:" . CUSTOM_DOMAIN_EMAIL . "\">". CUSTOM_DOMAIN_EMAIL ."</a> and request a sub domain name change.";
		echo "<br><br>For more information on to use this feature visit.</div>";
		echo "<div class=\"custom-domain-link\"><a href=\"https://support.bluinf.com\" target=\"_blank\">Technical Support</a></div>";
		
		}
	}

	add_action( 'wu_custom_domain_after', 'custom_domain_text', 10, 2 );

	function custom_domain_text_css () {
	echo '<style>
	.custom-domain-step {
		font-size: 15px!important;
		color: black!important;
	}
	.custom-domain-step p {
	font-size: 14px!important;
		color: black!important;
	}
	.custom-domain-status {
			font-size: 16px !important;
			color: black !important;
			background: #ddd;
			text-align: center;
			font-weight:bold !important;
	}

	.custom-domain-success {
			font-size: 14px !important;
			color: black !important;
			background: lightgreen;
			text-align: center;
			font-weight:bold !important;  
	}
	.custom-domain-warning {
			font-size: 14px !important;
			color: black !important;
			background: yellow;
			text-align: center;
			font-weight:bold !important;
	} 
	.custom-domain-error {
			font-size: 14px !important;
			color: black !important;
			background: red;
			text-align: center;
			font-weight:bold !important;
	}

	.custom-domain-check {
			font-size: 14px !important;
			color: black !important;
			background: #white;
			text-align:center;
	}
	.custom-domain-text {
			font-size: 14px!important;
			color: black!important;
			font-weight: bold;
			text-align: center;
		}
		.custom-domain-link {
			font-size: 16px!important;
			color: black!important;
			background-color: white;
			font-weight: bold;
			text-align: center;
		}
	</style>';
	}

	add_action('admin_head', 'custom_domain_text_css');

	/*function update_ultimo_point () {
	"Point an A Record to the following IP Address <code>%s</code>." */

	function change_custom_domain_text ( $translated_text, $text, $domain ) {
	if( $translated_text == 'You can use a custom domain with your website.') {
		$server_addr = WU_Settings::get_setting('network_ip');
		$translated_text = "";
		echo "<div class=\"custom-domain-status\">Custom Domain Instructions</div>";
		echo "<div class=\"custom-domain-step\" style=\"color:red!important;\"><b><center>Caution: This is not for changing your sub-domain .</center></b></div>";
		echo "<br>";
		echo "<div class=\"custom-domain-step\"><b>Step 1</b> - Register your domain name at <a href=\"https://namecheap.com/\">Namecheap</a></div>";
		echo "<br>";
		echo "<div class=\"custom-domain-step\"><b>Step 2</b> - Change your domain names DNS to the following:";
		echo "<p>Create an <b>A</b> Record for <b>@</b> to point to <b>$server_addr</b>";
		echo "<br>Create an <b>A</b> Record for <b>www</b> to point to <b>$server_addr</b></div>";
		echo "<div class=\"custom-domain-step\"><b>Step 3</b> - Enter in your domain name and extension (yoursite.com) below. Do not enter in www in the front of your domain.</div>";
	}

	if( $translated_text == 'Point an A Record to the following IP Address <code>%s</code>.') {
		$translated_text = "";
	}
	if( $translated_text == 'You can also create a CNAME record on your domain pointing to our domain <code>%s</code>.') {
		$translated_text = "";
	}
	return $translated_text;
	}
	add_filter( 'gettext', 'change_custom_domain_text', 20, 3 );

}