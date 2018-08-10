<?php

class ApiCallManager
{  
    // Number of item shown in one page
    const NO_OF_ITEMS = 25;
    const BASE_URL = 'https://hacker-news.firebaseio.com/v0/%s.json';

    /** 
     * Gets all new storiy ids from Hacker News 
     */
    public function getNewStories(int $page): array
    {    
        return $this->paginatedResult('newstories', $page);
    }    

    /** 
     * Gets all job story ids from Hacker News
     */
    public function getJobStories($page): array
    {      
        return $this->paginatedResult('jobstories', $page);
    }

    /** 
     * Gets all ask story ids from Hacker News
     */
    public function getAskStories(int $page): array
    {      
        return $this->paginatedResult('askstories', $page);
    }

     /** 
     * Gets all show story ids from Hacker News
     */
    public function getShowStories(int $page): array
    {      
        return $this->paginatedResult('showstories', $page);
    }

    /**
     * Get all comments
     */
    public function getAllComments(): array
    {      
        $str = file_get_contents('https://hacker-news.firebaseio.com/v0/newstories.json');
        
        $arr = $this->paginatedResult($str);

        $itemDetails = $arr['results'];

        $comments = array_map($this->getComments, $itemDetails);

        return [
            'comments' => $comments,
            'page' => $arr['page'],
            'maxPages' => $arr['totalNuberOfPages']
        ];
    }    

    /**
     * Fetches user info
     */
    public function getUserInfo(string $userId): ?array
    {
        $str = file_get_contents(sprintf('https://hacker-news.firebaseio.com/v0/user/%s.json', $userId));
        
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

    /**
     * Gets comments of one item
     */
    private function getComments($item)
    {
        $comments = $item['kids'];

        return $comments;
    }

    //** Handles all item id's and makes urls */
    private function getAllItemUrls($itemId)
    { 
        return sprintf('https://hacker-news.firebaseio.com/v0/item/%s.json', $itemId);
    }

    /**
     * Gets info and paginates it
     */
    private function paginatedResult(string $url, int $page): array
    {
        $str = file_get_contents(sprintf(self::BASE_URL, $url));
        $arr = $this->jsonDecode($str);
        $totalNumberOfItems = count($arr);
        $perPage = self::NO_OF_ITEMS;
        $totalNuberOfPages = $totalNumberOfItems / $perPage;
        $offset = $page <= 1 ? 0 : ($page - 1) * $perPage;
        
        $newIdArray = array_slice( $arr, $offset, $perPage );
        $resultUrls = array_map([$this, 'getAllItemUrls'], $newIdArray);
        $itemDetails = $this->getItemDetails($resultUrls);

        return [
            'results' => $itemDetails,
            'page' => $page,
            'maxPages' => $totalNuberOfPages
        ];
    }

    /**
     * Gets info and paginates it
     */
    private function paginatedJobResults(string $pageName, int $page): array
    {
        $str = sprintf(self::BASE_URL, $pageName);
        $arr = $this->jsonDecode($str);
        $totalNumberOfItems = count($arr);
        $perPage = self::NO_OF_ITEMS;
        $totalNuberOfPages = $totalNumberOfItems / $perPage;
        $offset = $page <= 1 ? 0 : ($page - 1) * $perPage;
        
        $newIdArray = array_slice( $arr, $offset, $perPage );
        $resultUrls = array_map([$this, 'getAllItemUrls'], $newIdArray);
        $itemDetails = $this->getItemDetails($resultUrls);

        return [
            'results' => $itemDetails,
            'page' => $page,
            'maxPages' => $totalNuberOfPages
        ];
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
            $results[] = $this->jsonDecode(curl_multi_getcontent($curlArr[$i]));
        }

        return $results;
    }
}
