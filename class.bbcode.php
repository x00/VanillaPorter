   <?php
   class BBCode{   
   private static $ParseList = 'noparse|php|mysql|code|strike|quote|cite|hide|url|img|thread|post|size|font|color|indent|left|list|flash|youtube|gvideo|vimeo|email|b|i|u|s';

   private static function NeedParse($Raw){
	return preg_match("`\[/?(".self::$ParseList.")[^\]]*\]`i",$Raw);
   }
   //raw pre parsed method, not suitable for direct for output!!!
   public static function Parse($Raw) {
	      if(self::NeedParse($Raw)){
			//~ echo "hi";
			//use catchall ([^a-z\=\]][^\]]*)? at the end of open/close tags so parse noisy tags, always parsing more specific first, to prevent stripping
		       $Raw = preg_replace("#\[(/)?noparse([^a-z\=\]][^\]]*)?\]#i",'<\\1pre>',$Raw);
		       $Raw = preg_replace("#\[(/)?(php|mysql|css|code)([^a-z\=\]][^\]]*)?\]#i", "<\\1code>", $Raw);
		       
               $Raw = preg_replace("#\[quote=[\"']?(.*)?;[0-9]+( [^\]]+)?\]#i",'<blockquote rel="\\1">',$Raw);
		       $Raw = preg_replace("#\[quote=[\"']?([^\"'\]]+)[\"']?([^a-z\=\]][^\]]*)?\]#i",'<blockquote rel="\\1">',$Raw);
		       $Raw = preg_replace("#\[(/)?(quote|cite)([^a-z\=\]][^\]]*)?\]#i",'<\\1blockquote>',$Raw);
		       $Raw = preg_replace("#\[(/)?hide([^a-z\]][^\]]*)?\]#i",'',$Raw);
		       
		       $Raw = preg_replace("#\[url=[\"']?([^\"'\]]+)[\"']?([^a-z\=\]][^\]]*)?\]([^\[]+)\[/url([^a-z\=\]][^\]]*)?\]#i",'<a target="_blank" href="\\1">\\3</a>',$Raw);
		       $Raw = preg_replace("#\[url=[\"']?([^\"'\]]+)[\"']?([^a-z\=\]][^\]]*)?\]\s*(\[/url[^a-z\=][^\]]*)?\]#i",'<a target="_blank" href="\\1">\\1</a>',$Raw);
		       $Raw = preg_replace("#\[url=[\"']?([^\"'\]]+)[\"']?[^\]]*\]([^\n\r\[]*)#si",'<a target="_blank" href="\\1">\\2</a>',$Raw);
		       $Raw = preg_replace("#\[url([^a-z\=\]][^\]]*)?\]([^\[]*)\[/url([^a-z\=\]][^\]]*)?\]#i",'<a  target="_blank" href="\\2">\\2</a>',$Raw);
		       $Raw = preg_replace("#\[/url([^a-z\]][^\]]*)?\]#si",'',$Raw);
		       
		       
		       $Raw = preg_replace("#\[img=[\"']?([^\"'\]]+)[\"']?([^a-z\=\]][^\]]*)?\]([^\[]+)\[/img([^a-z\=\]][^\]]*)?\]#i",'<img="_blank" href="\\1" border="0" alt="\\3">',$Raw);
		       $Raw = preg_replace("#\[img=[\"']?([^\"'\]]+)[\"']?([^a-z\=\]][^\]]*)?\]\s*(\[/img[^a-z][^\]]*)?\]#i",'<img src="\\2" border="0" />',$Raw);
		       $Raw = preg_replace("#\[img=[\"']?([^\"'\]]+)[\"']?[^\]]*\]([^\n\r\[]*)#si",'<img src="\\1" border="0"/>\\2',$Raw);
		       $Raw = preg_replace("#\[img([^a-z\=\]][^\]]*)?\]([^\[]*)\[/img([^a-z\=\]][^\]]*)?\]#i",'<img src="\\2" border="0" />',$Raw);
		       $Raw = preg_replace("#\[/img([^a-z\]][^\]]*)?\]#i",'',$Raw);
		       
		       $Raw = preg_replace("#\[(size|font|color)=[\"']?([^\"'\]]+)[\"']?([^a-z\=\]][^\]]*)?\]#i",'<font \\1="\\2">',$Raw);
		       $Raw = preg_replace("#\[/(size|font|color)([^a-z\]][^\]]*)?\]#i", "</font>", $Raw);
		       
		        $Raw = preg_replace(array('#\[indent([^a-z\=\]][^\]]*)?\]#i', '#\[/indent([^a-z\=\]][^\]]*)?\]#i'), array('<div class="Indent">', '</div>'), $Raw);
		        $Raw = preg_replace("#\[(/)?left([^a-z\]][^\]]*)?\]#i", '', $Raw);
		       
		       //handle order an unordered lists with callback
		       $Raw = preg_replace_callback("#\[list(=)[\"']?([^\"'\]]+)[\"']?([^a-z\=\]][^\]]*)?\](.*?)\[/list([^a-z\=\]][^\]]*)?\]#si",array('BBCode', 'ListCallback'),$Raw);
		       $Raw = preg_replace_callback("#\[list([^a-z\=\]][^\]]*)?\](.*?)\[/list([^a-z\=\]][^\]]*)?\]#si",array('BBCode', 'ListCallback'),$Raw);
		       
		       //strip flash tags (will be auto detected)
		       $Raw = preg_replace("#\[/?(flash|youtube|gvideo|vimeo|email)([^a-z\=\]][^\]]*)?\]#i",'',$Raw);
		       
		       $Raw = preg_replace("#\[(/)?strike([^a-z\]][^\]]*)?\]#i",'<\\1s>',$Raw);
		       
		       
		       $Raw = preg_replace("#\[(/)?b([^a-z\]][^\]]*)?\]#i",'<\\1b>',$Raw);
		       $Raw = preg_replace("#\[(/)?i([^a-z\]][^\]]*)?\]#i",'<\\1i>',$Raw);
		       $Raw = preg_replace("#\[(/)?u([^a-z\]][^\]]*)?\]#i",'<\\1u>',$Raw);
		       $Raw = preg_replace("#\[(/)?s([^a-z\]][^\]]*)?\]#i",'<\\1s>',$Raw);
		       //remove unparsed/malformed tags to clean up (limited)
			$Raw=preg_replace("#\[/?(".self::$ParseList.")[^\]]*\]#i",'',$Raw);
		}
	       
               return $Raw ;
 }
 
   protected static function ListCallback($Matches) {
      $ListOrderedType='';
      $NumForce = false;
      if($Matches[1]=='='){
	$ListType = 'ol';
	$ListOrderedType = ' type="'.$Matches[2].'"';
	$Content = $Matches[4];
      }else{
	 if(preg_match("`[\n\r]+\s*[0-9a-z]+[\.\s]\s*`i",$Matches[2],$m)>0){
		$ListType = 'ol';
		$NumForce = true;
	 }else{
		$ListType = 'ul';
	}
	$Content =$Matches[2];
      }
      
     
      if(self::NeedParse($Content)){
	$Content = self::Parse($Content);
      }
      $Content ="\n".$Content ;
      if(!$NumForce )
		$Content=preg_split("`([\n\r]+\s*\[\*([^\]]*)?\]\s*)`i",$Content);
      else
		$Content=preg_split("`([\n\r]+\s*[0-9a-z]+[\.\s]\s*)`i", $Content);
      $Result = '';
      foreach ($Content as $Item) {
	//$Item = preg_replace("`^[0-9a-z]+[\.\s]\s*`i",'',$Item);
         if (trim($Item) != '') $Result .= "<li>".trim($Item)."</li>\n";
      }
      $Result = '<'.$ListType.$ListOrderedType.">\n".$Result.'</'.$ListType .'>';
      return $Result;
   }
}
?>
