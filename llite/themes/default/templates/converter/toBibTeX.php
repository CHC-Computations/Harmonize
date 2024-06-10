@<?= strtolower($this->record->get('majorFormat')) ?>{<?= $this->record->getId()?>,
author = "<?= $this->record->get('mainAuthor','persons') ?>",	
title = "<?= $this->record->getTitle() ?>",	
publisher = "<?= $this->record->get('coAuthor','corporates')?>",	
year = "<?= $this->record->get('publicationYear')?>"
}
