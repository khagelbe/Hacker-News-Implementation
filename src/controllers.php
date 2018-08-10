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

// define controllers for one ask details
$asking = $app['controllers_factory'];
$asking->get('/', function ($id) use ($app) {
    $result = new ApiCallManager();
    return $app['twig']->render('ask_read.html.twig', array(
        'result' => $result->getAskDetails($id)
    ));
})
->bind('asking');

// define controllers for story
$story = $app['controllers_factory'];
$story->get('/', function (Request $request) use ($app) {
    $result = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('story.html.twig', array(
        'results' => $result->listAllNewStories($page)
    ));
})
->bind('story');

// Define controllers for comment
$comment = $app['controllers_factory'];
$comment->get('/', function ($page) use ($app) {
    $result = new ApiCallManager();
    return $app['twig']->render('comment.html.twig', array(
        'results' => $result->listAllNewStories($page)
    ));
});

// Define controllers for job
$job = $app['controllers_factory'];
$job->get('/', function ($page) use ($app) {
    $result = new ApiCallManager();
    return $app['twig']->render('job.html.twig', array(
        'results' => $result->listAllJobs($page)
    ));
});

// Define controllers for ask stories
$ask = $app['controllers_factory'];
$ask->get('/', function ($page) use ($app) {
    $result = new ApiCallManager();
    return $app['twig']->render('ask.html.twig', array(
        'results' => $result->listAllAskStories($page)
    ));
});

// Define controllers for show stories
$show = $app['controllers_factory'];
$show->get('/', function ($page) use ($app) {
    $result = new ApiCallManager();
    return $app['twig']->render('show.html.twig', array(
        'results' => $result->listAllShowStories($page)
    ));
});

// Defines controllers for user
$user = $app['controllers_factory'];
$user->get('/', function ($id) use ($app) {
    $result = new ApiCallManager();
    return $app['twig']->render('user.html.twig', array(
        'result' => $result->getUserInfo($id)
    ));
})
->bind('user');

$app->mount('/story/{$page}', $story);
$app->mount('/comment/{$page}', $comment);
$app->mount('/job/{$page}', $job);
$app->mount('/ask/{$page}', $ask);
$app->mount('/show/{$page}', $show);
$app->mount('/user/{id}', $user);
$app->mount('/asking/{id}', $asking);

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
