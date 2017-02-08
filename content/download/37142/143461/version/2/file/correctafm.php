<?
/**********************************************************************************************************
 *
 * AUTHOR			: Sreejith	(sreejith@marlabs.com
 * CREATED			: 2004-Jul-28
 * PURPOSE			: To help ezpublish users to create fonts of
 * their own in an easy way.
 *
 */
************************************************************************************************************/
	$arg=$_SERVER['argv'];
	if(strstr($arg[1],".afm"))
	{
		$fp=fopen("$arg[1]","r");
		$str=$contents = fread ($fp, filesize ($arg[1]));
		$mycnt=substr_count($contents,"C -1");	
		for($i=1;$i<=$mycnt;$i++)
		{
			$str=preg_replace("/C -1/","C $i","$str",1);	
		}
		fclose($fp);
		$fp=fopen("$arg[1]","w");
		if(!(fwrite($fp,$str)))
		{
			echo "Could not write to file";
			exit;
		}
		echo "Success";
	}



?>
