<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
$app = new Silex\Application();

$app['debug'] = true;

$m = new MongoClient();
$db = $m->silexblog;


$collection = $db->silex_blog_db;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'\..\views'
));


$app->get('/', function() use ($app, $collection) {
  $output = '';
  $posts = $collection->find();
  foreach($posts as $post) {
    $output .= "<a href='/index.php/".$post['slug']."'>".$post['title']."</a><br />";
  }
  return $output; // $app['twig']->render('home.html.twig');
});


$app->get('/new', function() use ($app) {
  return $app['twig']->render('new.html.twig');
});

$app->get('/{slug}', function($slug) use ($app, $collection) {
  $post = $collection->find(['slug' => $slug]);
  $data = '';
  foreach ($post as $p) {
    $data .= $p['body'];
  }
  return $data;
});

$app->post('/new', function(Request $request) use ($app, $collection) {
  $post = [
    "title" => $request->request->get('title'),
    "body"  => $request->request->get("body"),
    "slug"  => preg_replace('/[^A-Za-z0-9-]+/', '-', $request->request->get('title'))
  ];
  $collection->insert($post);
  return $app->redirect('/');
});


$app->run();