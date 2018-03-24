<?php
    
    function parseEpisodePage(DomNodeList $divs, int &$numDays): array {
        $episodePage = [];
        foreach ($divs as $div) {
            if ($div->hasAttribute('class') && $div->getAttribute('class') === 'episode-music') {
                if (!$numDays) {
                    break;
                }
                $songs = [];
                foreach ($div->childNodes as $row) {
                    $children = $row->childNodes[0]->childNodes;
                    $songs[] = [
                        'title' => $children[0]->nodeValue,
                        'artist' => $children[1]->nodeValue
                    ];
                }
                $numDays--;
                $episodePage[] = $songs;
            }
            
        }
        
        return $episodePage;
        
    }