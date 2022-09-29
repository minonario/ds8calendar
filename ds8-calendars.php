<?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

class FDCalendar {

    private $dates;
    /**
     * Constructor
     */
    public function __construct($dates){
        //$this->naviHref = htmlentities($_SERVER['PHP_SELF']);
      $this->dates = $dates;
    }

    private $dayLabels = array("sem","L","M","M","J","V","S","D");    
    private $monthLabels = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
    private $currentYear=0;
    private $currentMonth=0;
    private $currentDay=0;
    private $currentDate=null;
    private $daysInMonth=0;
    private $naviHref= null;
    
    private function show_filter($post_id){
        $dropdown = '';
        $args = array(  
            'post_type' => 'calendar',
            'post_status' => 'publish',
            'calendar_cat' => 'actual',
            'orderby' => 'title',
            'order' => 'ASC'
        );

        $categories = wp_get_post_terms($post_id, 'calendar_cat', array('fields' => 'names'));

        $loopx = new WP_Query( $args ); 
        if ( $loopx->have_posts() ) {
          $dropdown = '<select class="ds8country_dp">';
          while ( $loopx->have_posts() ) : $loopx->the_post(); 
              $url = get_permalink();
              $post_categories = $this->get_post_primary_category(get_the_ID()); 
              $primary_category = $post_categories['primary_category'];
              $dropdown .= '<option value="'.$url.'" '.(in_array($primary_category->name, $categories) ? 'selected' : '').'>'.$primary_category->name.'</option>';
          endwhile;
          $dropdown .= '</select>';
        }
        wp_reset_postdata();
        return $dropdown;
    }
    
    private function compareByTimeStamp($dt1, $dt2) {
        $parsed_dt1 = date_parse_from_format("j/n/Y", $dt1);
        $parsed_dt2 = date_parse_from_format("j/n/Y", $dt2);
        $dt1_ = strtotime($parsed_dt1['year'].'-'.$parsed_dt1['month'].'-'.$parsed_dt1['day']);
        $dt2_ = strtotime($parsed_dt2['year'].'-'.$parsed_dt2['month'].'-'.$parsed_dt2['day']);
        return $dt1_ - $dt2_;
    }

    /**
    * print out the calendar
    */
    public function show($yeax, $post_id = 0) {
        //$yeax  = null;
        $month = null;

        if(null==$yeax&&isset($_GET['yeax'])){
            $yeax = $_GET['yeax'];
        }else if(null==$yeax){
            $yeax = date("Y",time());
        }

        if(null==$month&&isset($_GET['month'])){
            $month = $_GET['month'];
        }else if(null==$month){
            $month = date("m",time());
        }

        $this->currentYear=$yeax;
        $this->currentMonth=$month;
        $this->daysInMonth=$this->_daysInMonth($month,$yeax);
        
        $dropdown = $this->show_filter($post_id);
        if ( $this->dates !== null){
          uksort($this->dates, array('FDCalendar', 'compareByTimeStamp'));
        }
        
        $categories = wp_get_post_terms($post_id, 'calendar_cat', array('fields' => 'names'));
        /*if (array_search( 'Actual', $categories )){
          unset( $categories[array_search( 'Actual', $categories )] );
        }*/
        
        $terms = get_terms( array(
            'taxonomy' => 'calendar_cat',
            'hide_empty' => false,
        ) );
        $terms_ = array_column($terms, 'name');
        $terms_names = array_diff($terms_, $categories);
        unset( $terms_names[array_search( 'Actual', $terms_names )] );
        
        $keys = array_map(function ($term) {
          return $term->term_id;
        }, array_filter($terms, function ($term) use ($terms_names) {
          return in_array($term->name, $terms_names);
        }));
        
        $content='<div class="ds8pagination">';
        $content.= $dropdown;
        $content.='<div class="customfd">';
        $content.='<div class="next">'. get_next_post_link( '%link &raquo;', '%title', $in_same_term = true, $keys, 'calendar_cat' ).'</div>';
        $content.='<div class="prev">'. get_previous_post_link( '&laquo; %link', '%title', $in_same_term = true, $keys, 'calendar_cat' ).'</div>';
        $content.='</div>';
        $content.='</div>';

        $content.='<div id="fdcalendarsx">'.
                    '<div class="calyr">'.
                      '<div class="calmain">';
        
        for( $month=1; $month<=12; $month++){
        
            $content.='<div class="calyrmo">'.
                        '<table class="month">'.
                          '<tbody>';
                          $content.= "<tr><th colspan='8'><h4>{$this->monthLabels[$month-1]}</h4></th></tr>";
                          $content.= $this->_createLabels();
                          $this->currentMonth = $month;
                          $this->daysInMonth=$this->_daysInMonth($month,$yeax);
                          $this->currentDay = 0;

                          $weeksInMonth = $this->_weeksInMonth($month,$yeax);
                          // Create weeks in a month
                          for( $i=0; $i<$weeksInMonth; $i++ ){
                              $rows='';
                              $content.='<tr>';
                              //Create days in a week
                              for($j=1;$j<=7;$j++){

                                  if ( $j == 1) {
                                     $rows.= '<td class="wno">&nbsp;</td>';
                                  }

                                  $dd = $i*7+$j;
                                  $dayof = idate('w', mktime(0, 0, 0, $month, $dd , $yeax));

                                  if ($dayof == 0){
                                    $weeknumber = idate('W', mktime(0, 0, 0, $month, $dd , $yeax));
                                  }
                                  $rows.=$this->_showDay($dd);
                              }
                              $rowsx = preg_replace('/<td class="wno">([^>]*?)<\/td>/', "<td class='wno'>{$weeknumber}</td>", $rows);
                              $content.=$rowsx.'</tr>';
                          }
                          if ($weeksInMonth == 5){
                            $content.='<tr><td colspan="8">&nbsp;</td></tr>';
                          }
                          if ($weeksInMonth == 4){
                            $content.='<tr><td colspan="8">&nbsp;</td></tr>';
                            $content.='<tr><td colspan="8">&nbsp;</td></tr>';
                          }

                          $content.='</tbody>';
              $content.='</table>';
            $content.='</div>';
        }

                    $content.='</div>';
              $content.='</div>';
        $content.='</div>';
        
        if (isset($this->dates) && is_array($this->dates)){
          $content .= '<div class="fddays">';
          $content .= '<h2>Feriados</h2>';
          $content .= '<table class="dates-descriptions">';
          $content .= '<tbody>';
          foreach ($this->dates as $date => $description){
            $parsed = date_parse_from_format("j/n/Y", $date);
            //$content .= '<p><span style="display:inline-block; width: 30px; text-align: right; padding-right: 10px">'.$parsed['day'].'</span><span style="padding-right: 20px;width: 100px; display:inline-block;">'.ucwords($this->monthLabels[$parsed['month']-1]).'</span> '.$description.'</p>';
            $content .= '<tr><td class="day redday">'.$parsed['day'].'</td><td class="month redday">'.ucwords($this->monthLabels[$parsed['month']-1]).'</td><td class="desc">'.$description.'</td></tr>';
          }
          $content .= '</tbody>';
          $content .= '</table>';
          $content .= '</div>';
        }

        return $content;
    }

    /**
    * create the li element for ul
    */
    private function _showDay($cellNumber){

        if($this->currentDay==0){
            $firstDayOfTheWeek = date('N',strtotime($this->currentYear.'-'.$this->currentMonth.'-01'));
            if(intval($cellNumber) == intval($firstDayOfTheWeek)){
                $this->currentDay=1;
            }
        }

        if( ($this->currentDay!=0)&&($this->currentDay<=$this->daysInMonth) ){
            $this->currentDate = date('d/m/Y',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay)));
            //$this->currentDate = date('Y-m-d',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay)));
            $date = date("l", strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay)));
            $date = strtolower($date);
            if($date == "saturday" || $date == "sunday") {
              $color_class = 'bluday';
            }
            $cellContent = $this->currentDay;
            $this->currentDay++;
        }else{
            $this->currentDate =null;
            $cellContent=null;
        }
        // FEATURE ADD DATE SIGN
        if ($this->currentDate != null){
          if($this->currentMonth == 9){
            if ( isset($this->dates[$this->currentDate]) ){
              $redclass = 'redday';
            }
          }
        }

        return '<td '.($this->currentDate == null ? '' : 'id="li-'.$this->currentDate.'"').' class="'.(isset($this->dates[$this->currentDate]) ? 'boldd ': (isset($color_class) ? $color_class : '')).' '.($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).
                ($cellContent==null?'mask':'').'">'.(isset($this->dates[$this->currentDate]) ? '<a class="fddate" title="'.$this->dates[$this->currentDate].'">'.$cellContent.'</a>': $cellContent).'</td>';
    }

    /**
    * create navigation
    */
    private function _createNavi(){

        $nextMonth = $this->currentMonth==12?1:intval($this->currentMonth)+1;
        $nextYear = $this->currentMonth==12?intval($this->currentYear)+1:$this->currentYear;
        $preMonth = $this->currentMonth==1?12:intval($this->currentMonth)-1;
        $preYear = $this->currentMonth==1?intval($this->currentYear)-1:$this->currentYear;

        return
            '<div class="header">'.
                '<a class="prev" href="'.$this->naviHref.'?month='.sprintf('%02d',$preMonth).'&yeax='.$preYear.'">Prev</a>'.
                    '<span class="title">'.date('Y M',strtotime($this->currentYear.'-'.$this->currentMonth.'-1')).'</span>'.
                '<a class="next" href="'.$this->naviHref.'?month='.sprintf("%02d", $nextMonth).'&yeax='.$nextYear.'">Next</a>'.
            '</div>';
    }

    /**
    * create calendar week labels
    */
    private function _createLabels(){

        $content='<tr>';

        foreach($this->dayLabels as $index=>$label){
          $content.='<td class="'.($label==='sm' ? 'wno':'wdt').'">'.$label.'</td>';
        }

        return $content.'</tr>';
    }

    /**
    * calculate number of weeks in a particular month
    */
    private function _weeksInMonth($month=null,$yeax=null){

        if( null==($yeax) ) {
            $yeax =  date("Y",time());
        }

        if(null==($month)) {
            $month = date("m",time());
        }

        // find number of days in this month
        $daysInMonths = $this->_daysInMonth($month,$yeax);
        $numOfweeks = ($daysInMonths%7==0?0:1) + intval($daysInMonths/7);
        $monthEndingDay= date('N',strtotime($yeax.'-'.$month.'-'.$daysInMonths));
        $monthStartDay = date('N',strtotime($yeax.'-'.$month.'-01'));

        if($monthEndingDay<$monthStartDay){
            $numOfweeks++;
        }

        return $numOfweeks;
    }

    /**
    * calculate number of days in a particular month
    */
    private function _daysInMonth($month=null,$yeax=null){

        if(null==($yeax))
            $yeax =  date("Y",time());

        if(null==($month))
            $month = date("m",time());

        return date('t',strtotime($yeax.'-'.$month.'-01'));
    }
    
    private function get_post_primary_category($post_id, $term='calendar_cat', $return_all_categories=false){
    $return = array();

        if (class_exists('WPSEO_Primary_Term')){
            // Show Primary category by Yoast if it is enabled & set
            $wpseo_primary_term = new WPSEO_Primary_Term( $term, $post_id );
            $primary_term = get_term($wpseo_primary_term->get_primary_term());

            if (!is_wp_error($primary_term)){
                $return['primary_category'] = $primary_term;
            }
        }

        if (empty($return['primary_category']) || $return_all_categories){
            $categories_list = get_the_terms($post_id, $term);

            if (empty($return['primary_category']) && !empty($categories_list)){
                $return['primary_category'] = $categories_list[0];  //get the first category
            }
            if ($return_all_categories){
                $return['all_categories'] = array();

                if (!empty($categories_list)){
                    foreach($categories_list as &$category){
                        $return['all_categories'][] = $category->term_id;
                    }
                }
            }
        }

        return $return;
    }

}