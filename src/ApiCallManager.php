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
     * Info of one item including kids
     */
    public function getItem($id)
    {
        $str = file_get_contents(sprintf('https://hacker-news.firebaseio.com/v0/item/%s.json', $id));
        $details = json_decode($str, true); // Item details

        $kidsContents = [];
        if (array_key_exists('kids', $details) && !empty($details['kids'])) {
            $details['kids'] = $this->checkItemKids($details, 0);
        }

        return [
            'result' => $details
        ];
    }

    /**
     * Checks kids
     */
    private function checkItemKids(array $details): array  
    { 
        $kids = $details['kids'];  // Array of kids of item       
        $resultUrls = array_map([$this, 'getItemUrl'], $kids);
        $kidsContents = $this->getItemDetails($resultUrls); // Array of kids details of one main item
        
        foreach ($kidsContents as &$content) {
            if (array_key_exists('kids', $content) && !empty($content['kids'])) {
                $content['kids'] = $this->checkItemKids($content);
            }
        }

        return $kidsContents; die;
    }  

    private function getKids($itemArray)
    {
        return isset($itemArray['kids']);
    }

    /**
     * Fetches user info
     */
    public function getUserInfo(string $userId): ?array
    {
        $str = file_get_contents(sprintf('https://hacker-news.firebaseio.com/v0/user/%s.json', $userId));
        $result = json_decode($str, true); 
        
        return [
            'result' => $result
        ]; 
    } 

    //** Handles all item id's and makes urls */
    private function getItemUrl($itemId)
    { 
        return sprintf('https://hacker-news.firebaseio.com/v0/item/%s.json', $itemId);
    }

    /**
     * Gets info and paginates it
     */
    private function paginatedResult(string $url, int $page): array
    {
        $str = file_get_contents(sprintf(self::BASE_URL, $url));
        $arr = json_decode($str, true);
        $totalNumberOfItems = count($arr);
        $perPage = self::NO_OF_ITEMS;
        $totalNuberOfPages = ceil($totalNumberOfItems / $perPage);
        $offset = $page <= 1 ? 0 : ($page - 1) * $perPage;
        
        $newIdArray = array_slice( $arr, $offset, $perPage );
        $resultUrls = array_map([$this, 'getItemUrl'], $newIdArray);
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

        $results = [];
        for ($i = 0; $i < $arr_count; $i++) {
            $content = json_decode(curl_multi_getcontent($curlArr[$i]), true);
            if (!isset($content['deleted'])) {
                $results[] = $content;
            }           
        }

        return $results;
    }
}
