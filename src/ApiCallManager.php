<?php

class ApiCallManager

// Loading takes too much time. Fix it.
{  
    // Number of item shown in one page
    const NO_OF_ITEMS = 25;

    /** 
     * Gets all new storiy ids from Hacker News 
     */
    public function getNewStories(): array
    {      
        $str = file_get_contents('https://hacker-news.firebaseio.com/v0/newstories.json');
        $arr = $this->jsonDecode($str);

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $totalNumberOfItems = count($arr);
        $perPage = self::NO_OF_ITEMS;
        $totalNuberOfPages = $totalNumberOfItems / $perPage;
        $offset = $page <= 1 ? 0 : ($page - 1) * $perPage;
        
        $newIdArray = array_slice( $arr, $offset, $perPage );

        $resultUrls = array_map($this->getAllItemUrls(), $arr);

        $itemDetails = $this->getItemDetails($resultUrls);

        return [
            'results' => $itemDetails,
            'page' => $page,
            'maxPages' => $totalNuberOfPages
        ];
    }    

    /** 
     * List all jobs in detail
     */
    public function listAllJobs(): array
    {
        $resultArray = [];
        $jobsArray = $this->getJobStories();    

        return $this->getDetails($jobsArray);
    }

    /** 
     * List all ask stories in detail
     */
    public function listAllAskStories(): array
    {
        $resultArray = [];
        $askStoryArray = $this->getAskStories();    

        return $this->getDetails($askStoryArray);
    }

    /** 
     * Lists all show stories in detail
     */
    public function listAllShowStories(): array
    {
        $resultArray = [];
        $showStoryArray = $this->getShowStories();    

        return $this->getDetails($showStoryArray);
    }

    /**
     * Fetches info about ask
     */
    public function getAskDetails(string $itemId): array
    {
        $str = file_get_contents(sprintf('https://hacker-news.firebaseio.com/v0/item/%s.json', $itemId));
        return $this->jsonDecode($str);
    }

    /**
     * Fetches user info
     */
    public function getUserInfo(string $userId): ?array
    {
        $str = file_get_contents(sprintf('https://hacker-news.firebaseio.com/v0/user/%s.json', $userId));
        
        return $this->jsonDecode($str);       
    }

    /**
     * Gets info of one single item
     * Solution was found from: https://stackoverflow.com/questions/9308779/php-parallel-curl-requests
     * Normal looping doesn't work because it's too slow 
     */
    private function getItemDetails($arr) {
        $arr_count = count($arr);

        $curlArr = [];
        $master = curl_multi_init();

        for ($i = 0; $i < $arr_count; $i++) {
            $url = $arr[$i];
            $curlArr[$i] = curl_init($url);
            curl_setopt($curlArr[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($master, $curlArr[$i]);
        }

        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);

        for ($i = 0; $i < $arr_count; $i++) {
            $results[] = curl_multi_getcontent($curlArr[$i]);
        }
        //print_r($results);
        return $results;
    }

    /** 
     * Gets all ask story ids from Hacker News
     */
    private function getAskStories(): array
    {      
        $str = file_get_contents('https://hacker-news.firebaseio.com/v0/askstories.json');
        return $this->jsonDecode($str);
    }

    /** 
     * Gets all show story ids from Hacker News
     */
    private function getShowStories(): array
    {      
        $str = file_get_contents('https://hacker-news.firebaseio.com/v0/showstories.json');
        return $this->jsonDecode($str);
    }

    /** 
     * Gets all job story ids from Hacker News
     */
    private function getJobStories(): array
    {      
        $str = file_get_contents('https://hacker-news.firebaseio.com/v0/jobstories.json');
        return $this->jsonDecode($str);
    }

    private function getUser($userId): array
    {      
        $str = file_get_contents(sprintf('https://hacker-news.firebaseio.com/v0/user/%s.json', $userId));
        return $this->jsonDecode($str);
        //array_filter
    }

    /**
     * Decodes json string to array
     */
    private function jsonDecode(string $str): ?array
    {
        return json_decode($str, true);
    }

    private function getComments($item)
    {
        $comments = $item['kids'];

        return $comments;
    }

    //** Handles all item id's and makes urls */
    private function getAllItemUrls($itemId)
    {
        $url = sprintf('https://hacker-news.firebaseio.com/v0/item/%s.json', $itemId);
        return $url; 
    }
}
