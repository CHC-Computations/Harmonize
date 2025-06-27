<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new buffer($this)); 
echo $this->render('head.php'); 
echo $this->render('core/header.php'); 

?>
<div id="content">
	<div id="neo4jd3" style="height:65vh; min-height:65vh">
	</div>
</div>

        <!-- Scripts -->
        <script type="text/javascript">
            function init() {
                var neo4jd3 = new Neo4jd3('#neo4jd3', {
                    highlight: [
                        {
                            class: 'Project',
                            property: 'name',
                            value: 'neo4jd3'
                        }, {
                            class: 'User',
                            property: 'userId',
                            value: 'eisman'
                        }
                    ],
                    icons: {
                        'Address': 'home',
                        'Api': 'gear',
                        'BirthDate': 'birthday-cake',
                        'Cookie': 'paw',
                        'CreditCard': 'credit-card',
                        'Device': 'laptop',
                        'Email': 'at',
                        'Git': 'git',
                        'Github': 'github',
                        'Google': 'google',
                        'icons': 'font-awesome',
                        'Ip': 'map-marker',
                        'Issues': 'exclamation-circle',
                        'Language': 'language',
                        'Options': 'sliders',
                        'Password': 'lock',
                        'Phone': 'phone',
                        'Project': 'folder-open',
                        'SecurityChallengeAnswer': 'commenting',
                        'User': 'user',
                        'zoomFit': 'arrows-alt'},
                    images: {
                        'Address': 'https://eisman.github.io/neo4jd3/img/twemoji/1f3e0.svg',
                        'Api': 'https://eisman.github.io/neo4jd3/img/twemoji/1f527.svg',
                        'BirthDate': 'https://eisman.github.io/neo4jd3/img/twemoji/1f382.svg',
                        'Cookie': 'https://eisman.github.io/neo4jd3/img/twemoji/1f36a.svg',
                        'CreditCard': 'https://eisman.github.io/neo4jd3/img/twemoji/1f4b3.svg',
                        'Device': 'https://eisman.github.io/neo4jd3/img/twemoji/1f4bb.svg',
                        'Email': 'https://eisman.github.io/neo4jd3/img/twemoji/2709.svg',
                        'Git': 'https://eisman.github.io/neo4jd3/img/twemoji/1f5c3.svg',
                        'Github': 'https://eisman.github.io/neo4jd3/img/twemoji/1f5c4.svg',
                        'icons': 'https://eisman.github.io/neo4jd3/img/twemoji/1f38f.svg',
                        'Ip': 'https://eisman.github.io/neo4jd3/img/twemoji/1f4cd.svg',
                        'Issues': 'https://eisman.github.io/neo4jd3/img/twemoji/1f4a9.svg',
                        'Language': 'https://eisman.github.io/neo4jd3/img/twemoji/1f1f1-1f1f7.svg',
                        'Options': 'https://eisman.github.io/neo4jd3/img/twemoji/2699.svg',
                        'Password': 'https://eisman.github.io/neo4jd3/img/twemoji/1f511.svg',
                        'Phone': 'https://eisman.github.io/neo4jd3/img/twemoji/1f4de.svg',
                        'Project': 'https://eisman.github.io/neo4jd3/img/twemoji/2198.svg',
                        'Project|name|neo4jd3': 'https://eisman.github.io/neo4jd3/img/twemoji/2196.svg',
                        'SecurityChallengeAnswer': 'https://eisman.github.io/neo4jd3/img/twemoji/1f4ac.svg',
                        'User': 'https://eisman.github.io/neo4jd3/img/twemoji/1f600.svg',
                        'zoomFit': 'https://eisman.github.io/neo4jd3/img/twemoji/2194.svg'
						},
                    minCollision: 60,
                    neo4jDataUrl: 'https://eisman.github.io/neo4jd3/json/neo4jData.json',
                    nodeRadius: 25,
                    onNodeDoubleClick: function(node) {
                        switch(node.id) {
                            case '25':
                                // Google
                                window.open(node.properties.url, '_blank');
                                break;
                            default:
                                var maxNodes = 5,
                                    data = neo4jd3.randomD3Data(node, maxNodes);
                                neo4jd3.updateWithD3Data(data);
                                break;
                        }
                    },
                    onRelationshipDoubleClick: function(relationship) {
                        console.log('double click on relationship: ' + JSON.stringify(relationship));
                    },
                    zoomFit: true
                });
            }

            window.onload = init;
        </script>

<?=$this->render('core/footer.php');?>