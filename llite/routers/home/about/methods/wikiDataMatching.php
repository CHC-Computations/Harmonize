<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new buffer($this)); 

$this->setTitle($this->transEsc('How we match bibliographic data with wikidata'));

	echo $this->render('head.php');
	echo $this->render('core/header.php');

?>

	<div class="cms_box">
		<div class="container" id="content" style="background-color:rgba(255,255,255,0.8);">
			<div class="main">
				<h2>How do we present matches on the website?</h2>
				<div class="row">
					<div class="col-sm-3"><?= $this->render('helpers/matchLevel.php', ['matchLevel'=>1]); ?></div>
					<div class="col-sm-9">
						Such a symbol means that we are 100% sure of the fit. The bibliographic record contained a unique identifier next to the labels of person/place/etc. The presented data from wikidata included this identifier in the list of them external identifiers. 
						<br/><br/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-3"><?= $this->render('helpers/matchLevel.php', ['matchLevel'=>0.9]); ?></div>
					<div class="col-sm-9">
						The bibliographic record does not have an identifier next to the information presented. The label of an object in the bibliographic data overlaps 100% with any of the labels or aliases of a record (of the same type) in the wikidata. We found only one such match. Unfortunately, we still cannot be sure that another person/place/corporation with an identical name does not exist. Therefore, we consider this match to be fairly certain but not 100% 
						<br/><br/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-3"><?= $this->render('helpers/matchLevel.php', ['matchLevel'=>0.6]); ?></div>
					<div class="col-sm-9">
						The bibliographic record does not have an identifier next to the information presented. The percentage of coverage of the label sought is not 100% but still quite high. 						
						<br/><br/>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-3"><?= $this->render('helpers/matchLevel.php', ['matchLevel'=>0.2]); ?></div>
					<div class="col-sm-9">
						The coverage percentage of the label being searched for is quite low, but we still only find one match in the specified data type.
						We are completely unsure of this match. We leave this to your decision.

					</div>
				</div>
				
				<h1>How do we match bibliographic data with wikidata?</h1>
				<p><i>Here we can include an additional description of the algorithm used.</i></p>
				
				
			</div>
		</div>
	</div>

<?php

	echo $this->render('core/footer.php');

?>