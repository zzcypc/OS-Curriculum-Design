<?php
	session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title></title>
	</head>
	<body>
		<form>
			申请内存大小<input type="number" name="apply_size"/><br />
			<input type="submit" value="申请">
		</form>
		<form>
			释放作业号<input type="number" name="work_number"/><br />
			<input type="submit" value="释放">
		</form>


<?php
define("OK", 1);
define("ERROR", 0);
define("LIST_INIT_SIZE", 100);
define("LISTINCREMENT", 10);
define("MINSIZE", 10);

if(isset($_SESSION['count']))
{
	$count=$_SESSION['count'];
}
else
	$count=1;//程序计数器 

Class Item{
	var $start;
	var $size;
	var $state;
}

Class SqList{
	var $elem;//Item的数组
	var $length;
	var $listsize;

	function __construct() {
		//$this->elem=new Item; 
		$this->length=0; 
		$this->listsize=LIST_INIT_SIZE;  
	}

	function ListInsert($i,$e)//$e为Item
	{
		if($i<1||$i>$this->length+1)
			return ERROR;
		if($this->length>=$this->listsize)
		{
			$this->listsize=$this->listsize+LISTINCREMENT;
		}
		$q=$i-1;
		for($p=$this->length-1;$p>=$q;$p--)
			$this->elem[$p+1]=$this->elem[$p];
		$this->elem[$q]=$e;
		$this->length++;
		return OK;
	}

	function ListDelete($i,&$e)
	{
		if($i<1||$i>$this->length)
			return ERROR;
		$p=$i-1;
		$e=$this->elem[$p];
		$q=$this->length-1;
		for($p++;$p<=$q;$p++)
			$this->elem[$p-1]=$this->elem[$p];
		$this->length--;
		//printf("ok");
		return $e;
	}

	function LocateSize($size)
	{
		if($this->length==0)
			return 1;
		$i=1; /* i的初值为第1个元素的位序 */
	 	$p=0; /* p的初值为第1个元素的存储位置 */
		while($i<=$this->length && ($this->elem[$p]->size<$size))
	   	{
	   		$i++;
	    	$p++;
	   	}
		return $i;
	}

	function LocateStart($start)
	{
		$i=1; /* i的初值为第1个元素的位序 */
	 	$p=0; /* p的初值为第1个元素的存储位置 */
		while($i<=$this->length && ($this->elem[$p]->start!=$start))
	   	{
	   		$i++;
	    	$p++;
	   	}
	 	if($i<=$this->length)
	  		return $i;
	 	else
			return 0;
	}

	function LocateState($state)
	{
		$i=1; /* i的初值为第1个元素的位序 */
	 	$p=0; /* p的初值为第1个元素的存储位置 */
		while($i<=$this->length && ($this->elem[$p]->state!=$state))
	   	{
	   		$i++;
	    	$p++;
	   	}
	 	if($i<=$this->length)
	  		return $i;
	 	else
			return 0;
	}

	function ListDisplay()
	{
		echo "----------分区说明表----------<br/>"; 
		echo "分区号\t起址\t大小\t状态<br/>";
		for($i=0;$i<$this->length;$i++)
		{
			echo ($i+1)."\t".$this->elem[$i]->start."\t".$this->elem[$i]->size."\t";
			if($this->elem[$i]->state==0)
				echo "未分配<br/>";
			else
				echo "作业".$this->elem[$i]->state."<br/>"; 
		}
	}

	function SpareDisplay()
	{
		echo "----------空闲分区表----------<br/>"; 
		echo "表项号\t起址\t大小\t状态<br/>";
		for($i=0;$i<$this->length;$i++)
		{
			echo ($i+1)."\t".$this->elem[$i]->start."\t".$this->elem[$i]->size."\t未分配<br/>";
		}
	}

	function Data_json()
	{
		return json_encode($this);
	}

	function SpareInsert($e)//仅限Spare使用,$e为Item
	{
		$size=$e->size;
		$i=$this->LocateSize($size);
		$this->ListInsert($i,$e);	 
		return OK;
	}
}

 
function apply(&$Spare,&$L,$need,&$count)//L为Memory线性表 
{
	for($i=0;$i<$Spare->length;$i++)
	{
		if($Spare->elem[$i]->size>=$need)
		{
			$Stemp=new Item;
			if($Spare->elem[$i]->size-$need<MINSIZE)
			{
				
				$Spare->ListDelete($i+1,$Stemp);
				$j=$L->LocateStart($Stemp->start);
				$L->elem[$j-1]->state=$count;
				$count++;	
			}
			else
			{
				$Spare->ListDelete($i+1,$Stemp);//从空闲分区表中删除该表项，表项保存在Stemp中 
				$j=$L->LocateStart($Stemp->start);
				//printf("%d",j);
				//printf("%d %d %d",Stemp.start,Stemp.size,Stemp.state);
				
				$temp=new Item;
				$temp->start=$L->elem[$j-1]->start;
				$temp->size=$need;
				$temp->state=$count;
				$count++;
				$L->ListInsert($j,$temp);
				$L->elem[$j]->start=$L->elem[$j-1]->start+$L->elem[$j-1]->size;
				$L->elem[$j]->size=$L->elem[$j]->size-$need;
				
				$Stemp->start=$L->elem[$j]->start;//修改Stemp中的值 
				$Stemp->size=$L->elem[$j]->size;
				$Stemp->state=0;
				
				$Spare->SpareInsert($Stemp); 
				//printf("ok"); 
			}
		
			break;	
		}
			
	}
	return OK;
}


function free_memory(&$Spare,&$L,$state)
{
	$temp=new Item;
	$Stemp=new Item;
	$j=$L->LocateState($state);
	if($L->length==1)
	{
		$L->elem[$j-1]->state=0;
		$Spare->SpareInsert($L->elem[$j-1]);
		return OK;
	}
	if(($j==$L->length&&$L->elem[$j-2]->state==0)||$j!=1&&$j!=$L->length&&$L->elem[$j-2]->state==0&&$L->elem[$j]->state!=0)
	{
		//下为底部或1，上为0
		//echo "1";
		$k=$Spare->LocateStart($L->elem[$j-2]->start);
		$Spare->ListDelete($k,$temp);
		$L->ListDelete($j,$temp);
		$L->elem[$j-2]->size=$L->elem[$j-2]->size+$temp->size;
		$L->elem[$j-2]->state=0;
		$Spare->SpareInsert($L->elem[$j-2]);
		return OK;
	}
	if(($j==1&&$L->elem[$j]->state==0)||($j!=1&&$j!=$L->length&&$L->elem[$j-2]->state!=0&&$L->elem[$j]->state==0))
	{
		//echo "2";
		$k=$Spare->LocateStart($L->elem[$j]->start);
		$Spare->ListDelete($k,$temp);
		$L->ListDelete($j+1,$temp);
		$L->elem[$j-1]->size=$L->elem[$j-1]->size+$temp->size;
		$L->elem[$j-1]->state=0;
		$Spare->SpareInsert($L->elem[$j-1]);
		return OK;
	}
	if($j!=1&&$j!=$L->length&&$L->elem[$j-2]->state==0&&$L->elem[$j]->state==0)
	{
		//echo "3";
		$k=$Spare->LocateStart($L->elem[$j-2]->start);
		$Spare->ListDelete($k,$temp);
		$k=$Spare->LocateStart($L->elem[$j]->start);
		$Spare->ListDelete($k,$temp);
		$L->elem[$j-2]->size=$L->elem[$j-2]->size+$L->elem[$j-1]->size+$L->elem[$j]->size;
		$L->elem[$j-2]->state=0;
		$L->ListDelete($j,$temp);
		$L->ListDelete($j,$temp);
		$Spare->SpareInsert($L->elem[$j-2]);
		return OK;
	}
	if(($j==$L->length&&$L->elem[$j-2]->state!=0)||($j==1&&$L->elem[$j]->state!=0)||($j!=1&&$j!=$L->length&&$L->elem[$j-2]->state!=0&&$L->elem[$j]->state!=0))
	{
		//下为底，上为1；上为顶，下为1
		//echo "4";
		$L->elem[$j-1]->state=0;
		$Spare->SpareInsert($L->elem[$j-1]);
	}
	return OK;
}


	$Memory=new SqList;
	$Spare=new SqList;

	$temp=new Item;
	$temp->start=0;
	$temp->size=512;
	$temp->state=0;

	//$temp=array("start"=>0,"size"=>512,"state"=>0);
	$Memory->ListInsert(1,$temp);
	$Spare->ListInsert(1,$temp);

	if(isset($_SESSION["Spare"]))
	{
		//$Spare=json_decode($_SESSION["Spare"],1);
		$Spare->elem=$_SESSION['Spare']->elem;
		$Spare->length=$_SESSION['Spare']->length;
		$Spare->listsize=$_SESSION['Spare']->listsize;
	}
	if(isset($_SESSION["Memory"]))
	{
		$Memory->elem=$_SESSION["Memory"]->elem;
		$Memory->length=$_SESSION["Memory"]->length;
		$Memory->listsize=$_SESSION["Memory"]->listsize;
	}
	$apply_size=isset($_GET['apply_size'])?$_GET['apply_size']:"";
	$work_number=isset($_GET['work_number'])?$_GET['work_number']:"";
	if($apply_size)
	{
		apply($Spare,$Memory,$apply_size,$count);
	}
	if($work_number)
	{
		free_memory($Spare,$Memory,$work_number);
	}

	//apply($Spare,$Memory,30,$count);
	//apply($Spare,$Memory,50,$count);
	//apply($Spare,$Memory,70,$count);
	//apply($Spare,$Memory,50,$count);

	//free_memory($Spare,$Memory,1);
	//free_memory($Spare,$Memory,2);
	//free_memory($Spare,$Memory,3);
	//free_memory($Spare,$Memory,1);
	//free_memory($Spare,$Memory,4);
	//apply($Spare,$Memory,510,$count);
	//apply($Spare,$Memory,110,$count);
	//free_memory($Spare,$Memory,1);
	//echo "<textarea>";
	echo "<br/>";
	$Memory->ListDisplay();
	echo "<br/>";
	$Spare->SpareDisplay();
	$_SESSION['Spare']=$Spare;
	$_SESSION['Memory']=$Memory;
	$_SESSION['count']=$count;

	
	//print_r($_SESSION['Spare']);
	// echo "<pre>";
	// print_r($Spare);
	// echo "<pre/>";

	// echo "<pre>";
	// print_r($Memory);
	// echo "<pre/>";

	// echo "count".$count;
?>
	</body>
</html>
