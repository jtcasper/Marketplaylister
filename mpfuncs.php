<?php

    const DATE_FORM = 'mdY';

    
    function parseEpisodePage(DomNodeList $divs): array {
        $episodePage = [];
        foreach ($divs as $div) {
            if ($div->hasAttribute('class') && $div->getAttribute('class') === 'episode-music') {
                $songs = [];
                foreach ($div->childNodes as $row) {
                    $children = $row->childNodes[0]->childNodes;
                    $songs[] = [
                        'title' => $children[0]->nodeValue,
                        'artist' => $children[1]->nodeValue
                    ];
                }
                $episodePage[] = $songs;
            }
            
        }
        
        return $episodePage;
        
    }
    
    /**
     * Go through the DOM elements provided and pull out the Dates of all marketplace
     * pod episodes in the provided list.
     * 
     * @param DomNodeList $headers The elements with a header tag from the DOM
     * @param DateTime $lastDate The date of the most recent episode from the DB
     */
    function parseEpisodeDate(DomNodeList $headers, DateTime $lastDate): array {
      $episodeDates = [];
      foreach ($headers as $header) {
        if ($header->hasAttribute('class') && $header->getAttribute('class') === 'river--hed') {
          $episodeAnchorHref = $header->firstChild->getAttribute('href');
          $dateString = explode('/', $episodeAnchorHref)[3];
          $episodeDate = DateTime::createFromFormat(DATE_FORM, $dateString);
          if ($episodeDate < $lastDate) {
            break;
          }
          $episodeDates[] = $episodeDate;
        }
      }
      return $episodeDates;
    }
