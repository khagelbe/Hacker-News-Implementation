<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage');

// define controllers for new story page
$story = $app['controllers_factory'];
$story->get('/', function (Request $request) use ($app) {
    $apiManager = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('story.html.twig', $apiManager->getNewStories($page));
})
->bind('story');

// Define controllers for job page
$job = $app['controllers_factory'];
$job->get('/', function (Request $request) use ($app) {
    $apiManager = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('job.html.twig', $apiManager->getJobStories($page));
})
->bind('job');

// Define controllers for ask stories page
$ask = $app['controllers_factory'];
$ask->get('/', function (Request $request) use ($app) {
    $apiManager = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('ask.html.twig', $apiManager->getAskStories($page));
})
->bind('ask');

// Define controllers for read item page
$readItem = $app['controllers_factory'];
$readItem->get('/', function ($id) use ($app) {
    $apiManager = new ApiCallManager();
    return $app['twig']->render('read_item.html.twig', $apiManager->getItem($id));
})
->bind('read_item');

// Define controllers for show stories
$show = $app['controllers_factory'];
$show->get('/', function (Request $request) use ($app) {
    $apiManager = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('show.html.twig', $apiManager->getShowStories($page));
})
->bind('show');

// Defines controllers for user
$user = $app['controllers_factory'];
$user->get('/', function ($id) use ($app) {
    $apiManager = new ApiCallManager();
    return $app['twig']->render('user.html.twig', $apiManager->getUserInfo($id));
})
->bind('user');

$app->mount('/story', $story);
$app->mount('/job', $job);
$app->mount('/ask', $ask);
$app->mount('/show', $show);
$app->mount('/user/{id}', $user);
$app->mount('/item/{id}', $readItem);

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
