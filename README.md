This package sends a curl containing various data to AWS API Gateway which passes it on to an AWS Lambda function to transform a html (raw or url) to pdf and store it to the indicated s3 bucket/filepath/filename.

Use:
$html2pdf = new ThomasCMobley_Html2Pdf();
$parameters = [
    'apiurl'=>'https://yofd0pxi8l.execute-api.us-west-2.amazonaws.com/html2pdf/lambdapdf',
    'apikey'=>'47YexK2GHWapTiVm2aSGyalRylF5pvAkads7fgSJ',
    'data'=>$data,
    'bucket'=>'tclassifieds',
    'fontlinks'=>['https://fonts.googleapis.com/css?family=Lora'],
    'domain'=>'http://vstage.thomascmobley.com',
    'options'=>['footerCenter'=>'Page [page] of [toPage]']
];
$return = $html2pdf->transform($parameters);


apiurl, apikey and data are required.
data may be a URI (including the http(s)://) or raw html
fontlinks is a link to a cdn containing the desired fonts.  Fonts may also be loaded into the Fonts folder.
domain contains the actual domain raw html needs for loading stylesheets/js/images.
options is an array of any of the listed options contained in the head of the class 

Return: 
On success it returns an array containing bucket, filename and filepath.  
On failure it returns an array containing error information.
