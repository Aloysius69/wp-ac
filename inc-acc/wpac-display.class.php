<?php

class wpac_display{
    
    private static $div         = 'movieline'; 
    private static $divTitle    = 'movietitle';
    private static $span        = 'moviedata';
    private static $spanTab     = 'moviedatatab';
    
    private static $s_div       = 'movieline_s';
    private static $s_span      = 'moviedata_s';
    private static $s_spanTab   = 'moviedatatab_s';

    private static $t_div       = 'movieline_t';
    private static $t_spanTab   = 'moviedata_t';  
    
    private static $c_div       = 'customline'; 
    private static $c_divTitle  = 'customtitle';
    private static $c_span      = 'customdata';
    private static $c_spanTab   = 'customdatatab';
            
    /********************************************************************************/
    /* Retrieve display AlloCine data 
    /*
    /* @param string $movie
    /*
    /* @return display string
    /********************************************************************************/
    public static function Display($displayData, $dataType='movie'){
        
        switch($displayData['displayType']){
		case "excerpt":
                	return self::excerptDisplay($displayData, $dataType);
		case "tiny":
                	return self::tinyDisplay($displayData, $dataType);
		case "small":
                	return self::smallDisplay($displayData, $dataType);
		case "feature":
		case "extended":
                default:
		case "normal":
                	return self::normalDisplay($displayData, $dataType);
		case "custom":
                	return self::customDisplay($displayData, $dataType);
        }
        
    }
   
    /********************************************************************************/
    /* Retrieve display AlloCine data 
    /*
    /* @param string $movie
    /*
    /* @return display string
    /********************************************************************************/
    public static function customDisplay($displayData, $dataType='movie'){
        
        $design = unserialize(wpac_GetDefaultOptions()['customdesign']);
        $designHeader = array_shift($design);
        array_shift($design['fields']);
        
	$display = '<div class="customwrap">';
        foreach($design['fields'] as $k => $v){
            
            $w = $designHeader[$k.'width']['width'];
            if (intval($w) == 0) break 1;
            $display .= '<div class="custom'.$k.'" style="width: '.$w.';">';
        
            foreach($v as $key => $value){
                
                if ( $value['class'] == ''){
                    if ($k == 'top') { 
                        $c_div = self::$c_divTitle; 
                        $c_spanTab = self::$c_span;
                    }else{ 
                        $c_div = self::$c_div;
                        $c_spanTab = self::$c_spanTab;
                    }
                }else{
                    $c_div = $value['class']; 
                    if ($k == 'top') { 
                        $c_spanTab = self::$c_span;
                    }else{ 
                        $c_spanTab = self::$c_spanTab;
                    }
                }

                switch ($value['name']){
                    case 'title':
                    case 'production':
                    case 'directors':
                    case 'genre':
                        $display .= self::displayLine($value['valueContent'],          $displayData[$value['name']],          $c_div, $c_spanTab);
                        break 1;
                    case 'longsummary':
                    case 'shortsummary':
                    case 'cast':
                        $display .= self::displayLine($value['valueContent'],          $displayData[$value['name']],          $c_div, self::$c_span);
                        break 1;
                    case 'trailer':
                        if ( $displayData['trailerString'] != '' ) {
                            $display .= self::displayLine('', $displayData['trailerString'], $c_div, '');
                            $display .= self::displayLine('', $displayData['allocineString'], $c_div, '');
                        }
                        break 1;
                    case 'poster':
                        $display .= '<div class="customimg" style="width:'.$value['valueContent'].';">'.$displayData['imgString'].'</div>';
                        break 1;
                }
            }
            $display .= '</div>';
            
        }
        
	$display .= '</div>';
        
	return $display;
    }
    
    /********************************************************************************/
    /* Retrieve display AlloCine data 
    /*
    /* @param string $movie
    /*
    /* @return display string
    /********************************************************************************/
    public static function normalDisplay($displayData, $dataType='movie'){

	$display = '<div class="moviewrap">';
        if ($dataType == 'episode') {
            $display .= self::displayLine($displayData['series'], ' - '.__('Season', 'wp-ac').' '.$displayData['season']. ' '.__('Ep.', 'wp-ac').$displayData['episode'], self::$divTitle, self::$span);
            
        } else {
            $display .= self::displayLine($displayData['title'], '', self::$divTitle, self::$span);
        }
        $display .= '<div class="movietext">';
        
        switch ($dataType) {
            case 'episode':
                $display .= self::displayLine(__("Episode title", 'wp-ac'),         $displayData['title'],          self::$div, self::$spanTab);
                $display .= self::displayLine(__('Broadcast', 'wp-ac'),             $displayData['production'],     self::$div, self::$spanTab);
                $display .= self::displayLine(__('Director', 'wp-ac'),              $displayData['directors'],      self::$div, self::$spanTab);
                break;
            case 'series':
                $display .= self::displayLine(__('Series title', 'wp-ac'),          $displayData['title'],          self::$div, self::$spanTab);
                $display .= self::displayLine(__('Production', 'wp-ac'),            $displayData['production'],     self::$div, self::$spanTab);
                $display .= self::displayLine(__('Nb seasons', 'wp-ac'),            $displayData['nbseasons'],      self::$div, self::$spanTab);
                $display .= self::displayLine(__('Creators', 'wp-ac'),              $displayData['creators'],       self::$div, self::$spanTab);
                break;
            case 'movie':
                $display .= self::displayLine(__('Movie title', 'wp-ac'),           $displayData['title'],          self::$div, self::$spanTab);
                $display .= self::displayLine(__('Production', 'wp-ac'),            $displayData['production'],     self::$div, self::$spanTab);
                $display .= self::displayLine(__('Directors', 'wp-ac'),             $displayData['directors'],      self::$div, self::$spanTab);
                break;
        }
        
        $display .= self::displayLine(__('Genre', 'wp-ac'),                         $displayData['genre'],          self::$div, self::$spanTab);
        $display .= self::displayLine(__('Cast:', 'wp-ac'). ' ',                        $displayData['cast'],           self::$div, self::$span);
        
        if ($displayData['longsummary'] != '')
            $display .= self::displayLine(__('Summary:', 'wp-ac'). ' ',                 $displayData['longsummary'],    self::$div, self::$span);
        elseif ($displayData['shortsummary'] != '')
            $display .= self::displayLine(__('Summary:', 'wp-ac'). ' ',                 $displayData['shortsummary'],   self::$div, self::$span);
        
        if ( $displayData['trailerString'] != '' ) {
            $display .= self::displayLine('', $displayData['trailerString'], self::$div, '');
            $display .= self::displayLine('', $displayData['allocineString'], self::$div, '');
        }
	$display .= '</div>';
	$display .= '<div class="movieimg">';
	$display .= $displayData['imgString'];
	$display .= '</div>';
	$display .= '</div>';
        
	return $display;
    }
    
    /********************************************************************************/
    /* Retrieve display AlloCine data 
    /*
    /* @param string $movie
    /*
    /* @return display string
    /********************************************************************************/
    public function excerptDisplay($displayData, $dataType='movie'){
	
	$display = $displayData['title'] . ' - ' . $displayData['shortsummary'];
        
	return $display;
    }
   /********************************************************************************/
    /* Retrieve display AlloCine data 
    /*
    /* @param string $movie
    /*
    /* @return display string
    /********************************************************************************/
    public function smallDisplay($displayData, $dataType='movie'){
	
	$display = '<div class="moviewrap_s">';
	$display .= '<div class="movieimg_s">';
	$display .= $displayData['imgString'];
	$display .= '</div>';
	$display .= '<div class="movietext_s">';

        switch ($dataType) {

            case 'episode':
                
                $display .= self::displayLine(__('Series title', 'wp-ac'),      $displayData['series'],             self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Episode', 'wp-ac'),           __('Season', 'wp-ac').' '.$displayData['season'].' '.__('Ep.', 'wp-ac').$displayData['episode'].' - '.$displayData['title'],   self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Broadcast', 'wp-ac'),         $displayData['production'],         self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Directors', 'wp-ac'),         $displayData['directors'],          self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Cast:', 'wp-ac'). ' ',        $displayData['cast'],               self::$s_div, self::$s_span);
                if ($displayData['shortsummary'] != '')
                $display .= self::displayLine(__('Summary:', 'wp-ac'). ' ',     $displayData['shortsummary'],       self::$s_div, self::$s_span);
                break;

            case 'series':
                
                $display .= self::displayLine(__('Series title', 'wp-ac'),      $displayData['title'],              self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Production', 'wp-ac'),        $displayData['production'].' - '.$displayData['nbseasons'].' '.($displayData['nbseasons']==1 ? __('season', 'wp-ac') : __('seasons', 'wp-ac') ), self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Creators', 'wp-ac'),          $displayData['creators'],           self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Genre', 'wp-ac'),             $displayData['genre'],              self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Cast:', 'wp-ac'). ' ',        $displayData['cast'],               self::$s_div, self::$s_span);
                $display .= self::displayLine(__('Summary:', 'wp-ac'). ' ',     $displayData['shortsummary'],       self::$s_div, self::$s_span);
                break;
            
            case 'movie':
                
                $display .= self::displayLine(__('Production', 'wp-ac'),        $displayData['production'] . ' - ' . $displayData['directors'],     self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Genre', 'wp-ac'),             $displayData['genre'],              self::$s_div, self::$s_spanTab);
                $display .= self::displayLine(__('Cast:', 'wp-ac'). ' ',        $displayData['cast'],               self::$s_div, self::$s_span);
                $display .= self::displayLine(__('Summary:', 'wp-ac'). ' ',     $displayData['shortsummary'],       self::$s_div, self::$s_span);
                break;
            
        }
        
	$display .= '</div>';
	$display .= '</div>';
        
	return $display;
    }
    
   /********************************************************************************/
    /* Retrieve display AlloCine data 
    /*
    /* @param string $movie
    /*
    /* @return display string
    /********************************************************************************/
    public function tinyDisplay($displayData, $dataType='movie'){
        
	$display = '<div class="moviewrap_t">';
	$display .= '<div class="movieimg_t">' . $displayData['imgString'] . '</div>';
	$display .= '<div class="movietext_t">';
        
        switch ($dataType) {
            
            case 'episode':
                
                $display .= '<div class="' . self::$t_div . '"><span class="' . self::$s_span . '">' . $displayData['series'] . '</span>';
                $display .= ' ' . __('Season', 'wp-ac') . ' ' . $displayData['season'] . ' ' . __('Ep.', 'wp-ac') . $displayData['episode'] . ' - ' . $displayData['title'];
        	$display .= ', ' . __('directed by', 'wp-ac') . ' ' . $displayData['directors'] . ' ' . __('in', 'wp-ac') . ' ' . $displayData['production'];
                $display .= ', ' . __('with', 'wp-ac') . ' ' . $displayData['cast'] . '.</div>';
                if ($displayData['shortsummary'] != '')
                $display .= '<div class="' . self::$t_div . '"><span class="' . self::$t_spanTab . '">'.__('Summary:', 'wp-ac').' </span>' . $displayData['shortsummary'] . '</div>';
                break;
            
            case 'series':
                
                $display .= '<div class="' . self::$t_div . '"><span class="' . self::$s_span . '">' . $displayData['title'] . '</span>';
        	$display .= ', ' . __('created by', 'wp-ac') . ' ' .$displayData['creators'] . ' ' . __('in', 'wp-ac') . ' ' . $displayData['production'];
                $display .= ', ' . __('with', 'wp-ac') . ' '. $displayData['cast'] . '.';
                $display .= ' ' . __('Genre:', 'wp-ac') . ' ' . $displayData['genre'] . '.</div>';
                $display .= '<div class="' . self::$t_div . '"><span class="' . self::$t_spanTab . '">'.__('Summary:', 'wp-ac').' '.'</span>' . $displayData['shortsummary'] . '</div>';
                break;
            
            case 'movie':
                
                $display .= '<div class="' . self::$t_div . '"><span class="' . self::$s_span . '">' . $displayData['title'] . '</span>';
        	$display .= ', ' . __('directed by', 'wp-ac') . ' ' . $displayData['directors'] . ' ' . __('in', 'wp-ac') . ' ' . $displayData['production'];
                $display .= ', ' . __('with', 'wp-ac') . ' ' . $displayData['cast'] . '.';
                $display .= ' ' . __('Genre:', 'wp-ac') . ' ' . $displayData['genre'] . '.</div>';
                $display .= '<div class="' . self::$t_div . '"><span class="' . self::$t_spanTab . '">'.__('Summary:', 'wp-ac'). ' </span>' . $displayData['shortsummary'] . '</div>';
                break;
            
        }
	$display .= '</div>';
	$display .= '</div>';

        return $display;
    }


   /********************************************************************************/
    /* Retrieve display AlloCine data 
    /*
    /* @param string $movie
    /*
    /* @return display string
    /* return '<div class="movieline"><span class="moviedatatab">Titre du film</span>' . utf8_encode($movie->title) . '</div>';
    /********************************************************************************/   
    public static function displayLine($label, $data, $divClass, $spanClass=''){
        
        if ($spanClass == '') {
            $span = '';$espan = '';
        } else {
            $span = '<span class="' . $spanClass . '">';
            $espan = '</span>';
        }
    	return '<div class="' . $divClass . '">' . $span . $label . $espan . $data . '</div>';
        
    }

}

