<?php namespace Vpm3\Html2Pdf;

/*
 Below are the two methods of calling html2pdf
    http://wkhtmltopdf.org/usage/wkhtmltopdf.txt.  These must be camelCase 
    $html2pdf->setOption('footerCenter', 'Page [page] of [toPage]');
    $html2pdf->setOption('dpi', 72);
    $html2pdf->setBucket('tclassifieds');
    $html2pdf->setData($data);
    $html2pdf->setFilepath('files');
    $html2pdf->setFileName('my_file_name');
    $html2pdf->setFontLinks(['https://fonts.googleapis.com/css?family=Lora']);
    $html2pdf->setDomain('http://vstage.thomascmobley.com');
    $html2pdf->html2pdf();
can also be called as follows
    $parameters = [
        'bucket'=>'tclassifieds',
        'filepath'=>'files',
        'filename'=>'my_file_name',
        'data'=>$data,
        'fontlinks'=>['https://fonts.googleapis.com/css?family=Lora'],
        'domain'=>'http://vstage.thomascmobley.com',
        'options'=>['footerCenter'=>'Page [page] of [toPage]', 'dpi'=>72]
    ];
    $html2pdf->html2pdf($parameters);
Note:   bucket, data, filepath and filename are required, all others are optional
        fontlinks and domain are only used for raw html
*/

class Html2Pdf {
    // contains the url for the AWS api gateway
    private $apiUrl = null;
    // contains the url or raw data for the html to be transformed, loaded via the call to html2pdf
    private $data = null;
    // bucket to store the file into
    private $bucket = null;
    // path within the bucket to store the file into
    private $filePath;
    // name of the pdf stored
    private $fileName;
    // this should be loaded with any fonts needed to replace any missing fonts
    // these fonts should be listed as fallback fonts within the documents css files
    private $fontLinks = [];
    // change any css/js paths from relative to absolute (add the base url)
    private $domain = '';
    // API Gateway key
    private $apiKey = null;
    // options which may be used for the pdf
    private $options = [
        'dpi'=>null,                    // <dpi> Change the dpi explicitly (this has no effect on X11 based systems)
        'grayscale'=>null,              // PDF will be generated in grayscale
        'imageDpi'=>null,               // <integer> When embedding images scale them down to this dpi (default 600)
        'imageQuality'=>null,           // <integer> When jpeg compressing images use this quality (default 94)
        'lowQuality'=>null,             // <real> Generates lower quality pdf. Useful to shrink the result document space
        'marginBottom'=>null,           // <unitreal> Set the page bottom margin 
        'marginLeft'=>null,             // <unitreal> Set the page left margin (default 10mm)
        'marginRight'=>null,            // <unitreal> Set the page right margin (default 10mm)
        'marginTop'=>null,              // <unitreal> Set the page top margin 
        'orientation'=>null,            // Set orientation to Landscape or Portrait (default Portrait)
        'outline'=>null,                // Put an outline into the pdf (default)
        'noOutline'=>null,              // no outline 
        'outlineDepth'=>null,           // <int> outline depth
        'printMediaType'=>null,         // Use print media-type instead of screen
        'noPrintMediaType'=>null,       // Do not use print media-type instead of screen
        'background'=>null,             // Do print background (default)
        'noBackground'=>null,           // Do not print background
        'customHeader'=>null,           // <name> <value> Set an additional HTTP header (repeatable)
        'defaultHeader'=>null,          // Add a default header, with the name of the page to the left, and the page number to the right
        'disableForms'=>null,           // Do not turn HTML form fields into pdf form fields (default)
        'endableForms'=>null,           // Turn HTML form fields into pdf form fields
        'disableExternalLinks'=>null,   // Do not make links to remote web pages
        'enableExternalLinks'=>null,    // Make links to remote web pages (default)
        'disableInternalLinks'=>null,   // Do not make local links
        'enableInternalLinks'=>null,    // Make local links (default)
        'disableJavascript'=>null,      // Do not allow web pages to run javascript
        'enableJavascript'=>null,       // Do allow web pages to run javascript (default)
        'javascriptDelay'=>null,        // Wait some milliseconds for javascript finish (default 200)
        'minimumFontSize'=>null,        // <int> Minimum font size
        'pageOffset'=>null,                // <int> Set the starting page number (default 0)
        'password'=>null,               // <password> HTTP Authentication password
        'username'=>null,               // <username> HTTP Authentication username
        'disableSmartShrinking'=>null,  // Disable the intelligent shrinking strategy used by WebKit that makes the pixel/dpi ratio none constant
        'enableSmartShrinking'=>null,   // Enable the intelligent shrinking strategy
        'stopSlowScripts'=>null,        // Stop slow running javascripts (default)
        'noStopSlowScripts'=>null,      // Do not stop slow running javascripts (default)
        'disableTocBackLinks'=>null,    // Do not link from section header to toc (default)
        'enableTocBackLinks'=>null,     // Link from section header to toc (default)
        'userStyleSheet'=>null,         // <url> Specify a user style sheet, to load with every page
        'viewportSize'=>null,           // Set viewport size if you have custom scrollbars or css attribute overflow to
        'windowStatus'=>null,           // Wait until window.status is equal to this string before rendering page
        'pageHeight'=>null,             // <real> page height
        'pageSize'=>'letter',               //Set paper size to: A4, Letter, etc. (default A4)
        'pageWidth'=>null,              // <real> page width
        'noPdfCompression'=>null,       // Do not use lossless compression on pdf objects
        'title'=>null,                  // <text> The title of the generated pdf file (The title of the first document is used if not specified)
        'footerCenter'=>null,           // <text> Centered footer text
        'footerFontName'=>null,         // <name> Set footer font name (default Arial)
        'footerFontSize'=>null,         // <size> Set footer font size (default 12)
        'footerHtml'=>null,             // <url> Adds a html footer
        'footerLeft'=>null,             // <text> Left aligned footer text
        'footerRight'=>null,            // <text> Right aligned footer text
        'footerSpacing'=>null,          // <real> Spacing between footer and content in mm (default 0)
        'footerLine'=>null,             // Display line above the footer
        'noFooterLine'=>null,           // Do not display line above the footer (default)
        'headerCenter'=>null,           // <text> Centered header text
        'headerFontName'=>null,         // <name> Set header font name (default Arial)
        'headerFontSize'=>null,         // <size> Set header font size (default 12)
        'headerHtml'=>null,             // <url> Adds a html header
        'headerLeft'=>null,             // <text> Left aligned header text
        'headerRight'=>null,            // <text> Right aligned header text
        'headerSpacing'=>null,          // <real> Spacing between header and content in mm (default 0)
        'headerLine'=>null,             // Display line below the header
        'noHeaderLine'=>null,           // Do not display line below the header (default)
        'replace'=>null,                // <name> <value> Replace [name] with value in header and footer (repeatable)
        'disableDottedLines'=>null,     // Do not use dotted lines in the toc
        'tocHeaderText'=>null,          // <text> The header text of the toc (default Table of Contents)
        'tocLevelIndentation'=>null,    // <width> For each level of headings in the toc indent by this length (default 1em)
        'disableTocLinks'=>null,        // Do not link from toc to sections
        'tocTextSizeShrink'=>null,      // <real> For each level of headings in the toc the font is scaled by this factor (default 0.8)
        ];
    
    // URL for the AWS api gateway
    public function setApiUrl($apiUrl){
        $this->apiUrl = $apiUrl;
    }
    
    // Either a url or raw html
    public function setData($data){
        $this->data = $data;
    }
    
    // storage bucket
    public function setBucket($bucket){
        $this->bucket = $bucket;
    }
    
    // storage path
    public function setFilepath($filepath){
        $this->filePath = $filepath;
    }
    
    // storage name
    public function setFilename($filename){
        $this->fileName = $filename;
    }
    
    // AWS access key
    public function setAccessKey($accessKey){
        $this->accessKey = $accessKey;
    }
    
    // AWS secret key
    public function setAPIKey($apiKey){
        $this->apiKey = $apiKey;
    }
    
    public function setOption($name, $value){
        if (array_key_exists($name, $this->options)){
            $this->options[$name] = $value;
        } else {
            throw new Exception('Nonexistent property '.$name);
        }
    }
    
    // Either a single link or multiple links as an array
    public function setFontLinks($links){
        if (is_array($links)){
            $this->fontLinks = $links;
        } else {
            $this->fontLinks = [$links];
        }
    }
    
    // domain where stylesheets/js/images are located
    // this is important where we're processing raw html
    // ex: https://vbase.vpm3.com
    public function setDomain($domain){
        if (substr($domain, -1) == '/'){
            // remove the final / as we add that ourselves
            $domain = substr($domain, 0, strlen($domain)-1);
        }
        $this->domain = $domain;
    }
    
    /*
     *   INPUT:
     *      $parameters - array(
     *          array containing the following
     *              apiurl - string -- url of the api gateway, REQUIRED
     *              apikey - string -- aws.apigateway.key used to authenticate, REQUIRED
     *              data - string -- either a url or the raw html, REQUIRED
     *              bucket - string -- bucket to store to
     *              filepath - string -- file path to store to within the bucket
     *              filename - string -- filename to store the file to (no extension, .pdf added automatically)
     *              domain - string -- domain to be appended to css, image and js uri's, RAW HTML ONLY
     *              fontlinks - string -- link to needed css fonts, RAW HTML ONLY
     *              options - array([name=>value] where name is the name of any of the option values for $this->options
     *   RETURN:
     *      json decoded array, contains bucket, filepath, filename if no errors, else contains errors
    */
    public function transform($parameters = null) {
 		// Build request fields
        if ($parameters !== null){
            $this->setParameters($parameters);
        } else {
            throw Exception('Please enter your parameters');
        }
        if (!($err = $this->checkRequired())){
            throw new Exception($err);
        }
        if (stripos(substr($this->data, 0, 5), 'http') === false){
            // this is raw html, so alter any paths to point to stylesheets and add any font files
            $this->addDomain();
            $this->addFontFile();
        }
        
//$fp = fopen("/tmp/{$this->fileName}.html", 'w');
//fwrite($fp, $this->data);
//fclose($fp);
		$fields = [
			'bucket' 	=> $this->bucket,
			'filename' 	=> $this->fileName,
			'filepath' 	=> $this->filePath,
            'data'      => $this->data,
		];
        $options = $this->makeOptions();
        if (count($options)){
            $fields['options'] = $options;
        }
        $dataString = json_encode($fields);
		// Set up the curl headers
        $header = [
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($dataString)];
        //$this->apiKey = '47YexK2GHWapTiVm2aSGyalRylF5pvAkads7fgSJ';
        if ($this->apiKey !== null){
            $header[] = "x-api-key: {$this->apiKey}";
        }
        // set up curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);                                                                  
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($ch);
		curl_close($ch);
		// Parse response
		$return = json_decode($response);
        if (isset($return->errorMessage)){
            throw new Exception($return->errorMessage);
        } else if ($return === false){
            throw new Exception('Curl failure');
        } else {
            return $return;
        }
	}
    
    private function makeOptions(){
        $useOptions = [];
        foreach ($this->options as $option => $value){
            if ($value !== null) {
                $useOptions[$option] = $value;
            }
        }
        return $useOptions;
    }
    
    // set the domain for any referenced files from relative to absolute
    // the regex should only alter references which do not include http(s):
    private function addDomain(){
        $regex = '/=(\'|\")([A-Za-z\/]{1,})(\.css|\.js|\.jpg|\.png|\.gif|\.jpeg)/';
        $this->data = preg_replace($regex, '=$1'.$this->domain.'/$2$3', $this->data );
    }
    
    // set any needed font file links
    private function addFontFile(){
        if (count($this->fontLinks) > 0){
            foreach($this->fontLinks as $link){
                $this->data = str_replace('<head>', "<head>".PHP_EOL."<link href='{$link}' rel='stylesheet'>".PHP_EOL, $this->data);
            }
        }

    }
    
    // set parameters and options from array
    private function setParameters($parameters){
        foreach ($parameters as $parameter=>$values){
            switch ($parameter){
                case 'apiurl':
                    $this->setApiUrl($values);
                    break;
                case 'apikey':
                    $this->setAPIKey($values);
                    break;
                case 'data':
                    $this->setData($values);
                    break;
                case 'bucket':
                    $this->setBucket($values);
                    break;
                case 'filename':
                    $this->setFilename($values);
                    break;
                case 'filepath':
                    $this->setFilepath($values);
                    break;
                case 'filename':
                    $this->setFilename($values);
                    break;
                case 'fontlinks':
                    $this->setFontLinks($values);
                    break;
                case 'domain':
                    $this->setDomain($values);
                    break;
                case 'options':
                    if (is_array($values)){
                        foreach($values as $name=>$value){
                            $this->setOption($name, $value);
                        }
                    }
                    break;
            }
        }
    }
    
    // check that minimum parameters have been supplied
    private function checkRequired(){
        $err = '';
        if ($this->apiUrl == null){
            $err = 'Missing api gateway url';
        }
        if ($this->data == null){
            $err = 'Missing data';
        }
        if ($this->bucket == null){
            // load bucket with the default
            $this->bucket = 'vbaseprod';
        } 
        if (($this->fileName == null) || ($this->fileName == '')){
            // create a new filename 
            if (stripos(substr($this->data, 0, 5), 'http') === false){
                // md5 the raw html
                $this->fileName = md5($this->data);
            } else {
                // this is a url so md5 a combination of filename and time for uniqueness
                $this->fileName = md5($this->data.microtime());
            }
        }
        if (($this->filePath == null) || ($this->filePath == '')){
            // create the default filepath
            $this->filePath = 'files/'.date('Y').'/'.date('m').'/'.date('d');
        }
        return $err === ''? true : $err;
    }
}
