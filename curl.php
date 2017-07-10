<?php
ini_set('max_execution_time', $_GET['exec_time']);

$asy_req=$_GET['asyn_req'];
$params=array();
if(isset($_GET['host'])&&$_GET['host']!=null)
{
$hostaddr=$_GET['host'];
}
else 
{
echo "host not specified";
exit;
}



$rand_user_agent=0;
if(isset($_GET['rand_user_agent'])&&$_GET['rand_user_agent']!=null)
{
$rand_user_agent=$_GET['rand_user_agent'];
if($rand_user_agent==1)
include('rand_user_agent.php');
}

$rand_referer=0;
if(isset($_GET['rand_referer'])&&$_GET['rand_referer']!=null)
{
$rand_referer=$_GET['rand_referer'];
if($rand_referer==1)
include('rand_referer.php');
}

$rand_proxy='';
if(isset($_GET['rand_proxy'])&&$_GET['rand_proxy']!=null)
{
$rand_proxy=$_GET['rand_proxy'];
if($rand_proxy=='http')
include('rand_proxy.php');
}



if(isset($_GET['param_names'])&&isset($_GET['param_values'])) 
{
	if((count($_GET['param_names'],0)==count($_GET['param_values'],0)))
	{
   for($i=0;$i<count($_GET['param_names'],0);$i++)
   {
   $params[$_GET['param_names'][$i]]=urlencode($_GET['param_values'][$i]);
   }
   }
   else
   {
     echo "params not equal";   
   }
 
//url-ify the data for the POST
//foreach($params as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
//rtrim($fields_string, '&');

}





//set methods
if(isset($_GET['method'])&&$_GET['method']!=null) 
{
	$method=$_GET['method'];
	switch($method) 
	{
		case 'GET' :
		for($asy=0;$asy<$asy_req;$asy++)
		{
			$params_copy=$params;
		foreach($params_copy as $id =>$val)
		{ 
          if($val=="rand")
          $params_copy[$id]=$asy;
          		
		}
		if($_GET['show']==1)
		$para[$asy]=$params_copy;
		
		$curl[$asy] = curl_init();
		curl_setopt($curl[$asy], CURLOPT_URL, "$hostaddr"."?".http_build_query($params_copy));
	   }
		break;
		
		case 'POST':
		for($asy=0;$asy<$asy_req;$asy++)
		{
			$params_copy=$params;
		foreach($params_copy as $id =>$val)
		{
          if($val=="rand")
          $params_copy[$id]=$asy;		
		}
		if($_GET['show']==1)
		$para[$asy]=$params_copy;
		
		$curl[$asy] = curl_init();
		curl_setopt($curl[$asy], CURLOPT_URL, "$hostaddr");
		curl_setopt($curl[$asy],CURLOPT_POST, count($params_copy));
		curl_setopt($curl[$asy],CURLOPT_POSTFIELDS, http_build_query($params_copy));
	   }
		break;
		
		default:
       echo "unknown method detected";		 
		 exit;
		break;
	}
}
else 
{
	echo "method not specified";
	exit;
}


// set headers
if(isset($_GET['reqheaders'])&&count($_GET['reqheaders'],0)>0)
{ 
 $headcount=0;
  foreach($_GET['reqheaders'] as $head)
  {
  	$reqheaders[$headcount]=$head;
  $headcount++;
  }
  for($asy=0;$asy<$asy_req;$asy++)
		{
 curl_setopt($curl[$asy], CURLOPT_HTTPHEADER, $reqheaders);
     }
}










for($asy=0;$asy<$asy_req;$asy++)
		{
			if($rand_user_agent==1)
			{
			curl_setopt($curl[$asy], CURLOPT_USERAGENT,random_user_agent());
			}
			
			if($rand_referer==1)
			{
			curl_setopt($curl[$asy], CURLOPT_REFERER,random_referer());
		//	echo "<script>alert('referer');</script>";
			}

curl_setopt($curl[$asy], CURLOPT_RETURNTRANSFER, 1);

if($rand_proxy=='http')
{
$no_of_proxy=count($proxies,0);
curl_setopt($curl[$asy], CURLOPT_PROXY, $proxies[$asy % $no_of_proxy]);
}
curl_setopt($curl[$asy], CURLOPT_HEADER, 1);

if($_GET['show']==1)
curl_setopt($curl[$asy], CURLINFO_HEADER_OUT, true);
      }
      
      $mh = curl_multi_init();
      
      foreach($curl as $singlecurl)
      {
       curl_multi_add_handle($mh, $singlecurl);
      }
     
      

for($j=0;$j<$_GET['no_of_requests'];$j++)
{
// execute all queries simultaneously, and continue when all are complete
  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while ($running);


if($_GET['show']==1)
{
	for($asy=0;$asy<$asy_req;$asy++)
	 {
      $headerSent = curl_getinfo($curl[$asy], CURLINFO_HEADER_OUT );
      curl_close($curl[$asy]); 
      echo "<h2>Request Headers</h2>";
      echo "<pre>$headerSent</pre>";


      echo http_build_query($para[$asy]);
echo "<br>";

echo "<h2>Response</h2>";
echo "<pre>".htmlentities(curl_multi_getcontent($curl[$asy]))."</pre>";

}
}
else
{
echo "complete";
}
}









?>
