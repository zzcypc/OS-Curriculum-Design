<?php
	session_start();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<link rel="stylesheet" href="css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/style.css" />
		<title></title>
	</head>
	<body>
		<div class="container">
			<div class="page-header">
 				<h1 id="mytitle">最佳适应算法的模拟实现<small>(基于空闲分区表)</small></h1>
 				<p class="myparagraph">在Windows /Linux环境下实现，建立一张类似课本122页的分区使用表，并提供一组模拟作业，根据作业使用内存情况，参考课本125页分配流程，采用最佳适应算法，为其在分区使用表中分配内存。程序要能够在图形界面下形象显示作业，及表中信息。最后，对最佳适应算法和其它算法做出比较分析。</p>
			</div>
			<div class="container myform">
				<form class="col-xs-6">
				 	<div class="form-group">
				    	<label>申请内存大小(KB)</label>
				    	<input type="number" class="form-control" placeholder="申请内存大小" name="apply_size" min="1" />
				 	</div>
					<button type="submit" class="btn btn-primary">申请</button>
				</form>
				
				<form class="col-xs-6">
					<div class="form-group">
				    	<label>释放作业号</label>
				    	<input type="number" class="form-control" placeholder="释放作业号" name="work_number" min="1" />
					</div>
					<button type="submit" class="btn btn btn-warning">释放</button>
				</form>
			</div>

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
	
		function ListShow()
		{ 
			for($i=0;$i<$this->length;$i++)
			{
				$str=
					"<tr>"
						."<th scope=\"row\">".($i+1)."</th>"
						."<td>".$this->elem[$i]->start."</td>"
						."<td>".$this->elem[$i]->size."</td>";
				if($this->elem[$i]->state==0)
					$str=$str."<td>未分配</td></tr>";
				else
					$str=$str."<td>作业".$this->elem[$i]->state."</td></tr>"; 
				echo $str;
			}
		}
	
		function SpareShow()
		{
			for($i=0;$i<$this->length;$i++)
			{
				$str=
					"<tr>"
						."<th scope=\"row\">".($i+1)."</th>"
						."<td>".$this->elem[$i]->start."</td>"
						."<td>".$this->elem[$i]->size."</td>"
						."<td>未分配</td></tr>";
				echo $str;	
			}
	
		}
	
		function MemoryShow()
		{
			$arr=array("progress-bar-danger","progress-bar-warning","progress-bar-info");
			$index=0;
			for($i=0;$i<$this->length;$i++)
			{
				$percent=$this->elem[$i]->size/1024*100;			
				if($this->elem[$i]->state==0)
				{
					$str=
						"<div class=\"progress-bar progress-bar-success progress-bar-striped active\" style=\"width: ".$percent."%\">"
							."未分配"
						."</div>";
				}
				else
				{
					$style=$index++%3;
					$str=
					"<div class=\"progress-bar ".$arr[$style]." progress-bar-striped active\" style=\"width: ".$percent."%\">"
						."作业".$this->elem[$i]->state
					."</div>";		
				}
				echo $str;
			}
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
		$flag=0;
		for($i=0;$i<$Spare->length;$i++)
		{
			if($Spare->elem[$i]->size>=$need)
			{
				$flag=1;
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
				}
				break;	
			}
				
		}
		if($flag==0)
			echo "<script>alert('没有足够大的内存分区')</script>";
		return OK;
	}
	
	
	function free_memory(&$Spare,&$L,$state)
	{
		$temp=new Item;
		$Stemp=new Item;
		$j=$L->LocateState($state);
		if($j==0)
		{
			echo "<script>alert('该作业不存在')</script>";
			return ERROR;
		}
		if($L->length==1)
		{
			$L->elem[$j-1]->state=0;
			$Spare->SpareInsert($L->elem[$j-1]);
			return OK;
		}
		if(($j==$L->length&&$L->elem[$j-2]->state==0)||$j!=1&&$j!=$L->length&&$L->elem[$j-2]->state==0&&$L->elem[$j]->state!=0)
		{
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
			$L->elem[$j-1]->state=0;
			$Spare->SpareInsert($L->elem[$j-1]);
		}
		return OK;
	}
	
	$Memory=new SqList;
	$Spare=new SqList;

	$temp=new Item;
	$temp->start=0;
	$temp->size=1024;
	$temp->state=0;

	$Memory->ListInsert(1,$temp);
	$Spare->ListInsert(1,$temp);

	if(isset($_SESSION["Spare"]))
	{
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
	
	$_SESSION['Spare']=$Spare;
	$_SESSION['Memory']=$Memory;
	$_SESSION['count']=$count;
?>
	
			<div class="panel panel-default">
			  	<div class="panel-heading">内存示意图(1024KB)</div>
			  	<div class="panel-body">
					<div class="progress">
						<?php
							$Memory->MemoryShow();
						?>
					</div>
					<p>
						<span>0</span>
						<span class="text">1023</span>
					</p>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">分区说明表</div>
				<div class="panel-body">
				<table class="table table-hover">
			    	<thead>
			        <tr>
			        	<th>分区号</th>
			        	<th>起始地址</th>
			        	<th>大小(KB)</th>
			        	<th>状态</th>
			        </tr>
			    	</thead>
			    	<tbody>
				        <?php
				        	$Memory->ListShow();
				        ?>
			      	</tbody>
			    </table>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">空闲分区表</div>
				<div class="panel-body">
					<table class="table table-hover">
				    	<thead>
				        	<tr>
				        		<th>表项号</th>
				        		<th>起始地址</th>
				        		<th>大小(KB)</th>
				        		<th>状态</th>
				        	</tr>
				    	</thead>
				    	<tbody>
					      	<?php
					      		$Spare->SpareShow();
					      	?>
				    	</tbody>
				    </table>
				</div>
			</div>
			<div class="well">
				<p align="center">Copyright © 2016 草呓平川. All Rights Reserved. </p>
			</div>
		</div>
	</body>
</html>
