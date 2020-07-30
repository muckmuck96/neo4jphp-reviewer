<?php
use Silex\Application,
	Symfony\Component\HttpFoundation\Request,
	Everyman\Neo4j\Client,
	Everyman\Neo4j\Cypher\Query;

require __DIR__.'/vendor/autoload.php';

$app = new Application();
$app['debug'] = true;

$neo4j = new Everyman\Neo4j\Client('localhost', 7474);
$neo4j->getTransport()
	->setAuth('example4j', 'example4j');

$app->get('/', function () {
	return file_get_contents(__DIR__.'/static/index.html');
});


$app->get('/search', function (Request $request) use ($neo4j) {
	$searchTerm = $request->get('q');
	$query = '(?i).*'.$searchTerm.'.*';
	$queryTemplate = <<<QUERY
						MATCH (movie:Movie)<-[:REVIEWED]-()
						WHERE movie.title =~ {query}
						RETURN DISTINCT movie
						QUERY;

	$cypher = new Query($neo4j, $queryTemplate, array('query'=>$query));
	$results = $cypher->getResultSet();

	$movies = [];
	foreach ($results as $result) {
		$movies[] = array('movie' => $result['movie']->getProperties());
	}

	return json_encode($movies);
});

$app->get('/movie/{title}', function ($title) use ($neo4j) {
	$queryTemplate = <<<QUERY
						MATCH (movie:Movie {title:{title}})
						OPTIONAL MATCH (movie)<-[r]-(person:Person)
						RETURN movie.title as title,
							collect({name:person.name,
										job:head(split(lower(type(r)),'_')),
										role:r.roles,
										summary: r.summary,
										rating: r.rating}) as cast LIMIT 1
						QUERY;

	$cypher = new Query($neo4j, $queryTemplate, array('title'=>$title));
	$results = $cypher->getResultSet();
	$result = $results[0];

	$movie = array('title' => $result['title'], 'cast' => array());
	foreach ($result['cast'] as $member) {
		if(isset($member['rating'])) {
			$castMember = array(
				'job' => $member['job'],
				'name' => $member['name'],
				'role' => array(),
				'summary' => $member['summary'],
				'rating' => $member['rating']
			);
		} else {
			$castMember = array(
				'job' => $member['job'],
				'name' => $member['name'],
				'role' => array(),
			);
		}

		if ($member['role']) {
			foreach ($member['role'] as $name) {
				$castMember['role'][] = $name;
			}
		}

		$movie['cast'][] = $castMember;
	}

	return json_encode($movie);
});

$app->run();
