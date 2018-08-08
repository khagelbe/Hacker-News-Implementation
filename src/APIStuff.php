<?php

class APIStuff

//TODO Clean this file and remane it
// Loading takes too much time. Fix it.
{
    public function getNewStories()
    {      
        $str = file_get_contents('https://hacker-news.firebaseio.com/v0/newstories.json');
        $arr = json_decode($str, true);
        return $arr;
    }

    public function getTopStories()
    {
        return file_get_contents('https://hacker-news.firebaseio.com/v0/topstories.json');
    }

    public function getBestStories()
    {
        return file_get_contents('https://hacker-news.firebaseio.com/v0/beststories.json');
    }

    public function listAllNewStories()
    {
        $resultArray = [];
        $newsArray = $this->getNewStories();    

        // This $i is here just for testing purposes 
        // because getting result takes too much time and 
        // causes performance problems
        $i = 0;
        foreach ($newsArray as $newsId) {
            
            $str = file_get_contents('https://hacker-news.firebaseio.com/v0/item/' . $newsId . '.json'); 
            $resultArray[] = json_decode($str, true);
            $i++;
            
            if ($i >= 10) {
                return $resultArray;
            }
        }

        return $resultArray;
    }

    public function listAllTopStories()
    {
        $arr = $this->getTopStories();

        foreach ($arr as $newsId) {
            $news = file_get_contents('https://hacker-news.firebaseio.com/v0/item' . $newsId . '.json');
            $writer = $news['by'];
            $descendants = $news['descendants'];
            $id = $news['id'];
            $kids = $news['kids'];
            $score = $news['score'];
            $time = $news['time'];
            $title = $news['title'];
            $type = $news['type'];
            $url = $news['url'];
        }
    }

    public function listAllBestStories()
    {
        $arr = $this->getBestStories();

        foreach ($arr as $newsId) {
            $news = file_get_contents('https://hacker-news.firebaseio.com/v0/item' . $newsId . '.json');
            $writer = $news['by'];
            $descendants = $news['descendants'];
            $id = $news['id'];
            $kids = $news['kids'];
            $score = $news['score'];
            $time = $news['time'];
            $title = $news['title'];
            $type = $news['type'];
            $url = $news['url'];
        }
    }
}