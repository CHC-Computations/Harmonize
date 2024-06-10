<?php 
$currentCore = 'biblio';
# $facetsList = array_keys((array) $this->configJson->$currentCore->facets->solrIndexes) ?? [];
# sort($facetsList);	
$facetsList = ['all_wiki', 'author', 'author2', 'author_corporate', 'author_events_str_mv', 'centuries_str_mv', 'corporate_str_mv', 'edition', 'events_str_mv', 'format_major', 'genre_major', 
'genre_sub', 'isbn', 'issn', 'language', 'language_o_str_mv', 'linked_resource', 'linked_resource_id', 'magazines_str_mv', 'oclc_num', 'publishDate', 'publisher', 'record_contains', 
'source_db_str', 'source_db_sub_str', 'source_file', 'source_publication', 'subject_ELB_str_mv', 'subject_genre_str_mv', 'subject_nation_str_mv', 'subject_person_str_mv', 'subjects_str_mv', 
'topic', 'udccode_str_mv', 'with_roles_wiki'];

$exportFormats = $this->configJson->biblio->recordCard->exportFormats ?? [];

?>

  <div class="container" id="content">
	<div class="infopage">
		<h1>How to use our API?</h1>
			<h2>1. Searching in results</h2>
				<p>Currently, the European Literary Bibliography (ELB) service offers a GET REST API for reading bibliographic records.</p>
				<p></p>
				<h3>1.1. How to query?</h3>
				API url is: <br/>
				<?= $this->helper->pre( $this->buildURL('api/biblio') ) ?>
				<p>All query parameters are optional. If you omit any of the parameters, it will be used with a default value. Available parameters: </p>
				<ol>
					<li> <b>lookfor=</b> enter the text you are looking for. The text should be encoded in UTF-8 in a url-friendly format. <br/>
						For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam']) ) ?>
						or
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'żeromski']) ) ?>
					</li>
					<li> <b>type=</b> use if you want to search in specyfic field. Values available: <abbr title="default value"><code>allfields</code></abbr>, <code>title</code>, <code>author</code>. 
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'type'=>'author']) ) ?>
					</li>
					<li> <b>page=</b> by default <abbr title="default value"><code>1</code></abbr> is used for the first page of results. Put your value when you want to skip first pages of results. 
						The frirst value on yours results list will be <code>page*limit</code>.
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'type'=>'author', 'page'=>'4']) ) ?>
					</li>
					<li> <b>limit=</b> number of results per page. Minimum value avaible <code>1</code>, maximum value avaible <code><?= $this->configJson->api->maxLimit ?? 0 ?></code>, 
						default value <code>$this->configJson->api->limit</code>.
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'type'=>'author', 'page'=>'4', 'limit'=>50]) ) ?>
						Due to server performance limitations, <code>page * limit</code> must be less than <code><?= $this->configJson->api->maxResults ?></code>.<br/>
						If you need more results, use the ready-made downloads available at <a href="<?= $this->basicUri('home/sources')?>"><?= $this->basicUri('home/sources')?></a>.
					</li>
					<li> <b>sort=</b> use when you want to change order of the results. Values available: <?= $this->render ('helpers/codeList.php', ['values' => $this->configJson->api->sort, 'firstDefault'=>true]) ?>
						
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'type'=>'author', 'page'=>'4', 'limit'=>50, 'sort'=>'last_indexed+desc']) ) ?>
					</li>
					<li> <b>resultSize=</b> options available <abbr title="default value"><code>small</code></abbr>, <code>extended</code>.
						default <abbr title="default value"><code>small</code></abbr> value gives you id, title, author and lp (list position) values. The <code>extended</code> resultSize will give you all ELB relevant informations related with the result. 
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'type'=>'author', 'page'=>'4', 'limit'=>50, 'sort'=>'last_indexed+desc', 'resultSize'=>'extended']) ) ?>
					</li>
					</ol>
					<p>Extended options:</p>
					<ol>
					<li> <b>withFacets=</b> list the comma-separated names of the filter fields you want to get. Options available: <?= $this->render ('helpers/codeList.php', ['values' => $this->configJson->api->withFacets, 'firstDefault'=>false]) ?>
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'type'=>'author', 'page'=>'4', 'limit'=>50, 'sort'=>'last_indexed+desc', 'withFacets'=>'format_major,author']) ) ?>
					</li>
						<li> <b>useFacet=</b> it allows you to use filter values. The area you use to search must also be in the filter list listed in the withFace parameter. 
						In your query, separate the filter name from the value you are searching for with a colon. 
						You can use non-precise filters, e.g. <code>author:mickiewicz</code> (useful if you do not know the exact value of the filter) 
						or precise <code>format:"book"</code> (to avoid including e.g. the value "book chapter" in the results).<br/> 
						You can use <code>AND</code>, <code>OR</code>, <code>()</code> oprators to create more complex queries. 
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'withFacets'=>'format_major,author,language', 'useFacet'=>'format_major:"Book" AND language="Polish"']) ) ?>
						or
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'withFacets'=>'format_major,author,language', 'useFacet'=>'format_major:"Book" AND (language="Polish" OR language="Czech")']) ) ?>
					</li>
					<!--li> <b>facetQuery=</b> use if you don't know the filter value and you want to search in filters
						<br/>For example:
						<?= $this->helper->pre( $this->buildURL('api/biblio', ['lookfor'=>'adam', 'facetField'=>'author', 'facetQuery'=>'mickiewicz']) ) ?>
					</li-->
				</ol>
				<p></p>
				
				<h3>1.2. The answer</h3>
				<p>You will receive the response in json format. The main fields of the response are:</p>
				<ul>
				<li>totalResults - the number of all results your query returns </li>
				<li>docs - array with results </li>
				<li>facets - array or array with filter values specyfic for you query. First level keys are the names of the aspects you want. Second level keys are the strings available in that group. Values are the number of records with that string. </li>
				</ul>
			<h2>2. Single record query</h2>
				<p>When you need more information about a selected record or a copy of a record in a different format, you can use the single record query.</p>
				<p>The single record query has general format <b><?= $this->basicUri('results/biblio/record/{rec_id}.{rec_format}')?></b>.
				<p>instead of {rec_id} substitute the value of the record id from the list of results you got after searching the results. </p>
				<p>As {rec_format} you can use: <code>.<?= implode('</code>, <code>.',array_keys((array)$exportFormats)) ?></code></p>
				For example: 
				<?= $this->helper->pre( $this->basicUri('results/biblio/record/pl.pl000432825.json') )?>
				<?= $this->helper->pre( $this->basicUri('results/biblio/record/pl.pl000432825.mrk') )?>
				<?= $this->helper->pre( $this->basicUri('results/biblio/record/pl.pl000432825.marcxml') )?>
				
	</div>
  </div>
