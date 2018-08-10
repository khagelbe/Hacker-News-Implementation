<?php

class ApiCallManager

// Loading takes too much time. Fix it.
{  
    // Number of item shown in one page
    const NO_OF_ITEMS = 25;

    public function createPagination()
    {
        $page = $_GET['page'];
        $offset = ($page-1) * self::NO_OF_ITEMS;
        $totalNuberOfPages = $totalNumberOfPages / self::NO_OF_ITEMS;
    }

    /**
     * Lists all new stories in detail
     */
    public function listAllNewStories($page): array
    {
        $resultArray = [];
        $newsArray = $this->getNewStories();    

        return $this->getDetails($newsArray);
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
     * Gets all new storiy ids from Hacker News 
     * 
     */
    private function getNewStories(): array
    {      
        $str = file_get_contents('https://hacker-news.firebaseio.com/v0/newstories.json');

        $arr = $this->jsonDecode($str);

        $i = 0;
        
        foreach($arr as $item) {
            $resultArray[] = $item;
            $i++;

            if ($i>= self::NO_OF_ITEMS) {
                return $resultArray;
            }
        }
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
    }

    /**
     * Loops all ids from given array and returns item information
     */
    private function getDetails($arr): array
    {
        foreach ($arr as $itemId) {
            
            $str = file_get_contents(sprintf('https://hacker-news.firebaseio.com/v0/item/%s.json', $itemId)); 
            $resultArray[] = json_decode($str, true);
        }

        return $resultArray;
    }

    /**
     * Decodes json string to array
     */
    private function jsonDecode(string $str): ?array
    {
        return json_decode($str, true);
    }
}
