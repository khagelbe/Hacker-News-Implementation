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

// define controllers for a story page
$story = $app['controllers_factory'];
$story->get('/', function () use ($app) {
    $result = new APIStuff();
    return $app['twig']->render('story.html.twig', array(
        'results' => $result->listAllNewStories()
    ));
});

$comment = $app['controllers_factory'];
$comment->get('/', function () use ($app) {
    return $app['twig']->render('comment.html.twig', array());
});

$job = $app['controllers_factory'];
$job->get('/', function () use ($app) {
    return $app['twig']->render('job.html.twig', array());
});

$poll = $app['controllers_factory'];
$poll->get('/', function () use ($app) {
    return $app['twig']->render('poll.html.twig', array());
});

$user = $app['controllers_factory'];
$user->get('/', function () use ($app) {
    return $app['twig']->render('user.html.twig', array());
});

$app->mount('/story', $story);
$app->mount('/comment', $comment);
$app->mount('/job', $job);
$app->mount('/poll', $poll);
$app->mount('/user', $user);




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
