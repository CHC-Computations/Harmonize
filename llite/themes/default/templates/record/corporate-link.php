<?php 
	$linkStr = '';
	if (!empty($corporations)) 
		foreach ($corporations as $corp) 
			if (!empty($corp->name)) {
				$key = $this->buffer->createFacetsCode($this->sql, ["{$corp->field}:\"{$corp->solr_str}\""]);
				$link = $this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key );
				
				$linkStr = '<a href="'. $link .'" title="'. $this->transEsc('filter biblio with') .' '.$corp->name .'">'.$corp->name.'</a> ';
				
				if (!empty($corp->wikiq)) {
					$uid = uniqid();
					$boxClass = 'corporateBox'.$corp->wikiq;
					$linkStr .= '
						<div class="person-block" >
							<span id="button'.$uid.'"><i class="glyphicon glyphicon-info-sign" ></i></span>
							<div class="cloud-info '.$boxClass.'">
								<div class="pi-body" id="corporateBox'.$uid.'" >
									<div class="pi-Desc">
										'.$this->helper->loader2().' 
									</div>	
								</div>
							</div>
						</div>
							';
					$this->addJS("page.ajax('corporateBox".$uid."', '/wiki/corporate/box/{$corp->wikiq}');");		
					}
				
				if (!empty($corp->role))
					foreach ($corp->role as $role)
						$linkStr.= '<span class="role label label-info">'. $role .'</span>';
				$linkStr.='<br/>';
				}

?>
<?= $linkStr ?>