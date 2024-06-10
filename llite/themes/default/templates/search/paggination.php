
<ul class="pagination" aria-label="Paginacja">
<?php
if ($this->getCurrentPage() > 1) {
	echo '<li role="none">
			<a href="'.$this->buildUri('results/'.$currentCore,['page'=>'1']).'" aria-label="'.$this->transEsc('go to first page').'">
				<i class="fa fa-angle-double-left" aria-hidden="true"></i>
				<span class="sr-only">' .$this->transEsc('first page'). '</span>
			</a>
		</li>';
	echo '<li role="none">
			<a href="'.$this->buildUri('results/'.$currentCore,['page'=>$this->getCurrentPage()-1]).'" aria-label="'.$this->transEsc('go to previous page').'">
				<i class="fa fa-angle-left" aria-hidden="true"></i>
				<span class="sr-only">' .$this->transEsc('previous page'). '</span>
			</a>
		</li>';
	} else {
	echo '<li role="none" class="disabled"><a><i class="fa fa-angle-double-left" aria-hidden="true"></i><span class="sr-only">' .$this->transEsc('first page'). '</span></a></li>';
	echo '<li role="none" class="disabled"><a><i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">' .$this->transEsc('previous page'). '</span></a></li>';
	}
	
if ($this->getCurrentPage() < $this->getLastPage($currentCore)) {	
	echo '<li role="none">
			<a href="'.$this->buildUri('results/'.$currentCore,['page'=>$this->getCurrentPage()+1]).'" aria-label="'.$this->transEsc('go to next page').'">
				<i class="fa fa-angle-right" aria-hidden="true"></i>
				<span class="sr-only">' .$this->transEsc('next page'). '</span>
			</a>
		</li>';
	echo '<li role="none">
			<a href="'.$this->buildUri('results/'.$currentCore,['page'=>$this->getLastPage($currentCore)]).'" aria-label="'.$this->transEsc('go to last page').'" title="'.$this->transEsc('go to last page').' '.$this->getLastPage($currentCore).'">
				<i class="fa fa-angle-double-right" aria-hidden="true"></i>
				<span class="sr-only">' .$this->transEsc('last page'). '</span>
			</a>
		</li>';
	} else {
	echo '<li role="none" class="disabled"><a><i class="fa fa-angle-right" aria-hidden="true"></i><span class="sr-only">' .$this->transEsc('next page'). '</span></a></li>';
	echo '<li role="none" class="disabled"><a><i class="fa fa-angle-double-right" aria-hidden="true"></i><span class="sr-only">' .$this->transEsc('last page'). '</span></a></li>';
	}

?>
</ul>

<ul class="pagination" aria-label="Paginacja">

<?php 

$cp = $this->getCurrentPage();

for ($i = $cp-5; $i<=$cp+5; $i++) {
	if (($i>0)&($i<=$this->getLastPage($currentCore))) {
		if ($i == $this->getCurrentPage())
			$active = 'class="active"';
			else 
			$active = '';
		echo '<li role="none" '.$active.'>
				<a href="'.$this->buildUrl('results/'.$currentCore,['page'=>$i, 'facetsCode'=>$this->facetsCode ]).'" aria-label="'.$this->transEsc('go to page no').'" >
					<span class="sr-only">' .$this->transEsc('go to page no'). '</span>
					<span>'.$i.'</span>
				</a>
			</li>';
		}
	
	}
?>
  
</ul>


<?php 
	
	$sort = $this->getUserParam($currentCore.':sorting');
	#echo $sort;
	if (($sort == 'a') or ($sort == 't')) {
		$sl = $this->getParam('GET','swl');
		$menu = '';
		for ($i = 65; $i <=90 ; $i++) {
			$char = chr($i);
			if ($sl == $char)
				$menu .='<li class="active">';
				else 
				$menu .='<li>';
			$menu.= '<a href="'.$this->buildUri('results/'.$currentCore, ['page'=>'1','swl'=>$char]).'">'.$char.'</a></li>';
			}

		
		echo '<br/>
			<ul class="pagination">
				<li class="disabled">
					<a>'.$this->transEsc('Jump to letter').':</a>
				</li>
				'.$menu.'
				<li><a href="'.$this->buildUri('results/'.$currentCore, ['page'=>'1','swl'=>null]).'">'.$this->transEsc('All').'</a></li>
			</ul>';
		}
	

?>


