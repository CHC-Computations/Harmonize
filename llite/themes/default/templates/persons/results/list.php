<div class="pi-Body">
	<div class="pi-Image empty">
		<img src="<?= $this->HOST ?>themes/default/images/no_photo.svg" alt="no cover found" class="img img-responsive no-photo">
	</div>
	<div class="pi-Desc">
		<div class="pi-linkPanel"><?= $this->render('persons/linkPanel.php', ['AP' => $activePerson] ) ?></div>
		<div class="pi-head">
			<h4>
			  <a href="<?= $this->buildUri('search/results/1/'.$this->getUserParam('sort').'/', ['lookfor'=>urlencode($activePerson->name),'type'=>'AllFields'] ); ?>" title="<?= $this->transEsc('bibliographic results with').' '.htmlentities($activePerson->name)?>...">
				<?= htmlentities($activePerson->name) ?>
				<small><?= $this->render('persons/dateRange.php', ['b'=>$activePerson->year_born, 'd'=>$activePerson->year_death]) ?></small>
			  </a>
			</h4>
		</div>	
		<p></p>
		
	</div>
</div>