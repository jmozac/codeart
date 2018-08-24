<?php
	include 'libcurl.php';
	class crawl {
		var $url;
		var $rcheck=array();
		var $valid_urls=array();
		function extractlink($url=''){
			echo "checking ".$url."\n";
			$url = (substr($url,-1,1)=='/')?substr($url,0,strlen($url)-1):$url;
			# BOF CURL
			$curl=new czUrl;
			$curl->url=$url;
			$grab=$curl->fetch();
			$valid_urls=array();
			$rcode=array(200,301,302);
			if(in_array($grab['info']['http_code'],$rcode)){
				$input=$grab['html'];
			# EOF CURL
				$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
				$rlink=array();
				if(preg_match_all("/$regexp/siU", $input, $matches, PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$match[2]=str_replace('./','',$match[2]);
						if($this->is_link($match[2])){
							if(!in_array($match[2],$rlink)){
								if(substr($match[2],0,1)=='/') $match[2]=substr($match[2],1,strlen($match[2])-1);
								$host=parse_url($match[2],PHP_URL_HOST);
								if($host==str_replace(array('http://','https://'),'',$this->url) OR $host==''){
									if($host==''){
										$match[2] = (substr($this->url,-1,1)=='/')?$this->url.$match[2]:$this->url.'/'.$match[2];
									} 
									$rlink[]=$match[2];
									//echo "accept ".$match[2]."\n";
								}
							}
						} //else {
							//echo "ignore ".$match[2]."\n";
						//}
					}
				}
				for($i=0;$i<count($rlink);$i++){
					$valid_urls = $this->scanlink($rlink[$i]);
				}
			}
			return $valid_urls;
		}
		function scanlink($link){
			if(in_array($link,$this->rcheck)){
				//echo "checked ".$link."\n";
			} else {
				$this->rcheck[]=$link;
				if(filter_var($link,FILTER_VALIDATE_URL) !== false){
					$this->valid_urls[]=$link;
					$this->extractlink($link);
				} else {
					echo "invalid ".$link."\n";
				}
			}
			return $this->valid_urls;
		}
		protected function is_link($link){
			if(substr($link,0,1)=='#') return false;
			if(substr($link,0,3)=='../') return false;
			if(substr(strtolower($link),0,10)=='javascript') return false;
			if(substr(strtolower($link),0,6)=='mailto') return false;
			return true;
		}
	}

	$crawl=new crawl;
	$crawl->url=$argv[1];
	$rlink=$crawl->extractlink($argv[1]);
	
	echo "Total: ".count($rlink)."\n";
	print_r($rlink);
