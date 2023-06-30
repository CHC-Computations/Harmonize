<?php 


#echo "<pre>".print_r($this,1)."</pre>";

?>


<ul class="pagination" aria-label="Paginacja">
<?php
if ($this->getCurrentPage() > 1) {
	echo '<li role="none">
			<a href="'.$this->buildUri('persons/list',['page'=>'1']).'" aria-label="'.$this->transEsc('go to first page').'">
				<i class="fa fa-angle-double-left" aria-hidden="true"></i>
				<span class="sr-only">' .$this->transEsc('first page'). '</span>
			</a>
		</li>';
	echo '<li role="none">
			<a href="'.$this->buildUri('persons/list',['page'=>$this->getCurrentPage()-1]).'" aria-label="'.$this->transEsc('go to previous page').'">
				<i class="fa fa-angle-left" aria-hidden="true"></i>
				<span class="sr-only">' .$this->transEsc('previous page'). '</span>
			</a>
		</li>';
	} else {
	echo '<li role="none" class="disabled"><a><i class="fa fa-angle-double-left" aria-hidden="true"></i><span class="sr-only">' .$this->transEsc('first page'). '</span></a></li>';
	echo '<li role="none" class="disabled"><a><i class="fa fa-angle-left" aria-hidden="true"></i><span class="sr-only">' .$this->transEsc('previous page'). '</span></a></li>';
	}
	
if ($this->getCurrentPage() < $this->getLastPage()) {	
	echo '<li role="none">
			<a href="'.$this->buildUri('persons/list',['page'=>$this->getCurrentPage()+1]).'" aria-label="'.$this->transEsc('go to next page').'">
				<i class="fa fa-angle-right" aria-hidden="true"></i>
				<span class="sr-only">' .$this->transEsc('next page'). '</span>
			</a>
		</li>';
	echo '<li role="none">
			<a href="'.$this->buildUri('persons/list',['page'=>$this->getLastPage()]).'" aria-label="'.$this->transEsc('go to last page').'" title="'.$this->transEsc('go to last page').' '.$this->getLastPage().'">
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
	if (($i>0)&($i<=$this->getLastPage())) {
		if ($i == $this->getCurrentPage())
			$active = 'class="active"';
			else 
			$active = '';
		echo '<li role="none" '.$active.'>
				<a href="'.$this->buildUri('persons/list',['page'=>$i]).'" aria-label="'.$this->transEsc('go to page no').'">
					<span class="sr-only">' .$this->transEsc('go to page no'). '</span>
					<span>'.$i.'</span>
				</a>
			</li>';
		}
	
	}
?>
  
</ul>


