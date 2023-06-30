<?php

#$source_path = '/usr/local/vufind/tests/data';
$source_path = '/var/www/html/lite/import/data';
#$source_path = './import/tests'; 

$INDEXES = [
	'source_db_str'			=>	'995a',
	'source_db_sub_str'		=>	'995b',
	'format_major' 			=> 'getMajorFormat',
	'genre_major' 			=> 'getGenreM',
	'genre_sub' 			=> 'getGenreS',
	'fullrecord'			=> 'getSourceMrk', // 'saveJsonFile',
	'record_format' 		=> 'getRecFormat',
	'last_indexed' 			=> 'getCurrentTime',
	'language' 				=> 'getLanguageP',
	'language_o_str_mv' 	=> 'getLanguageO',
	
	'author' 				=> 'getMainAuthor',
	'author_variant'		=> 'getMainAuthorW',
	'author_sort'			=> 'getMainAuthorSort',
	'author_role'			=> 'getMainAuthorRole',
	
	'authorviaf_str_mv'		=> 'getAuthorViaf',
	'author2' 				=> 'getOtherAuthors',
	'author2_variant' 		=> 'getOtherAuthorsW',
	'author2_role' 			=> 'getMainOtherAuthorsRoles',
	
	'author_facet' 			=> 'getMainAuthor',
	'author2_facet' 		=> 'getOtherAuthors',
	
	'author_corporate' 		=> 'getCorporateAuthor',
	'corporate_str_mv' 		=> 'getCorporateAuthorFull',
		
	'title' 				=> 'getTitle',
	'title_sub' 			=> 'getTitleSub',
	'title_short' 			=> 'getTitleShort',
	'title_full' 			=> 'getTitleFull',
	'title_fullStr' 		=> 'getTitleFull',
	'title_sort' 			=> 'getTitleSort',
	'title_alt' 			=> 'getTitleAlt',
	
	'subjects_str_mv' 		=> 'getSubjects',
	'subject_person_str_mv' => 'getSubjectPersons',
	'subjectpersonviaf_str_mv' => 'getSubjectPersonsViaf',
	'topic'					=> 'getSubjects',
	'topic_search_str_mv'	=> 'getSubjectsFull',
	'subject_genre_str_mv'	=> 'getSubjectELBg',
	'subject_nation_str_mv'	=> 'getSubjectELBn',
	'subject_ELB_str_mv'	=> 'getSubjectELB',
	'author_events_str_mv'	=> 'getAuthorEvents',
	'events_str_mv'			=> 'getSubjectEvents',
	
	'publishDate' 			=> 'getPublishDate',
	'datesort_str_mv'		=> 'getPublishDate',
	
	'publisher'				=> 'getPublished',
	'publication_place_str_mv' => 'getIn',
	'container_title'		=> 	'773s',
	'source_publication'	=>	'getSourcePublication',
	
	'series'				=>	'getSeria',
	'series_str_mv'			=>	'getSeria',
	
	'edition'				=>	'getEdition',
	'geographic'			=>	'getRegion',
	'geographic_facet'		=>	'getRegion',
	'geographicpublication_str_mv'	=>	'getPublicationPlaces',
	'geoevents_str_mv'			=> 'getEventsPlace',
	'geowiki_str_mv'		=>	'getGeoWiki', 
	
	'responsibility_str_mv' =>	'getStatmentOfResp',
	'issn'					=> 	'getISSN',
	'magazines_str_mv'		=> 	'getMagazines',
	'article_issn_str'		=> 	'getArticleISSN',
	'isbn'					=> 	'getISBN',
	'oclc_num'				=>	'getOclcNum',
	'ctrlnum'				=>	'getCtrlNum',
	'work_keys_str_mv'		=> 	'getWorkKey',
	'article_resource_str_mv'=>	'773s',
	'article_resource_txt_mv'=>	'773t',
	'article_resource_related_str_mv' => '773g',
	'centuries_str_mv' 	=> 'getCenturies',
	'udccode_str_mv' 	=> 'getUDC',
	
	
	'author_wiki'			=> 'getMainAuthorWiki',
	'coauthor_wiki'			=> 'getCoAuthorWiki',
	'subject_person_wiki' 	=> 'getSubjectPersonsWiki',
	
	'geo_pub_wiki'			=> 'getPublicationPlacesWiki',
	'geo_sub_wiki'			=> 'getSubjectPlacesWiki',
	
	'geo_pub_country_wiki' 	=> 'getPublicationCountryWiki',
	
	'persons_str_mv'		=> 'getPersons',
	'persons_viaf_str_mv'	=> 'getPersonsViaf',
	'persons_wiki_str_mv'	=> 'getPersonsWiki',
	'geowikifull_str_mv'	=>	'getGeoWikiFull', 
	'spellingShingle' 		=>	'getSpellingShingle'
	];
	

$TEXT_INDEXES = [
	'format_major' 	=> 'getMajorFormat',
	'genre_major' 	=> 'getGenreM',
	'genre_sub' 	=> 'getGenreS',
	'genre'			=> 'getGenre',
	'language' 		=> 'getLanguage',
	'author' 		=> 'getMainAuthor',
	'author_sort'	=> 'getMainAuthorSort',
	'author_role'	=> 'getMainAuthorRole',
	'title_full' 	=> 'getTitleFull',
	'author2' 				=> 'getOtherAuthors',
	'author2_role' 			=> 'getMainOtherAuthorsRoles',
	'author_corporate' 		=> 'getCorporateAuthor',
	'subjects_str_mv' 		=> 'getSubjects',
	'subject_person_str_mv' => 'getSubjectPersons',
	'year2_str_mv' 			=> 'getPublishDate',
	'publication_place_str_mv' => 'getIn',
	'series'				=>	'getSeria',
	'edition'				=>	'getEdition',
	'geographic_facet'		=>	'getRegion',
	'responsibility_str_mv' =>	'getStatmentOfResp',
	'issn'					=> 	'getISSN',
	'article_issn_str'		=> 	'getArticleISSN',
	'isbn'					=> 	'getISBN',
	'oclc_num'				=>	'getOclcNum',
	'ctrlnum'				=>	'getCtrlNum',
	'container_title'		=> 	'773s',
	'article_resource_str_mv'=>	'773s',
	'article_resource_txt_mv'=>	'773t',
	'container_title_2'		=>	'getContainerTitle2',
	'info_resource_str_mv'	=>	'995a',
	'info_subresource_str_mv'=>	'995b',
	'article_resource_related_str_mv' => '773g',
	];


?>

