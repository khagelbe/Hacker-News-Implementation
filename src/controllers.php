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

// define controllers for one question
$asking = $app['controllers_factory'];
$asking->get('/', function ($id) use ($app) {
    $apiManager = new ApiCallManager();
    return $app['twig']->render('question.html.twig', array(
        'result' => $apiManager->getAskDetails($id)
    ));
})
->bind('asking');

// define controllers for new story page
$story = $app['controllers_factory'];
$story->get('/', function (Request $request) use ($app) {
    $apiManager = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('story.html.twig', $apiManager->getNewStories($page));
})
->bind('story');

// Define controllers for comment page
$comment = $app['controllers_factory'];
$comment->get('/', function (Request $request) use ($app) {
    $apiManager = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('comment.html.twig', $apiManager->getAllComments());
})
->bind('comment');

// Define controllers for job page
$job = $app['controllers_factory'];
$job->get('/', function (Request $request) use ($app) {
    $apiManager = new ApiCallManager();
    $page = $request->query->getInt('page', 1);
    return $app['twig']->render('job.html.twig', $apiManager->getJobs($page));
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

// Define controllers for question page
$question = $app['controllers_factory'];
$question->get('/', function ($id) use ($app) {
    $apiManager = new ApiCallManager();
    return $app['twig']->render('question.html.twig', array(
        'results' => $apiManager->listAllComments($id)
    ));
});

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
    return $app['twig']->render('user.html.twig', array(
        'result' => $apiManager->getUserInfo($id)
    ));
})
->bind('user');

$app->mount('/story', $story);
$app->mount('/comment', $comment);
$app->mount('/job', $job);
$app->mount('/ask', $ask);
$app->mount('/show', $show);
$app->mount('/user/{id}', $user);
$app->mount('/asking/{id}', $asking);
$app->mount('/question/{id}', $question);

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
