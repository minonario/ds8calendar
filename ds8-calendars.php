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

    private $dayLabels = array("sm","l","m","m","j","v","s","d");    
    private $monthLabels = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
    private $currentYear=0;
    private $currentMonth=0;
    private $currentDay=0;
    private $currentDate=null;
    private $daysInMonth=0;
    private $naviHref= null;

    /**
    * print out the calendar
    */
    public function show($yeax) {
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

        $content='<div id="fdcalendarsx">'.
                    '<div class="calyr">'.
                      '<div class="calmain">';
        
        for( $month=1; $month<=12; $month++){
        
            $content.='<div class="calyrmo">'.
                        '<table class="month">'.
                          '<tbody>';
                          $content.= "<tr><th colspan='8'>{$this->monthLabels[$month-1]}</th></tr>";
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

                          $content.='</tbody>';
              $content.='</table>';
            $content.='</div>';
        }

                    $content.='</div>';
              $content.='</div>';
        $content.='</div>';
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

        return '<td '.($this->currentDate == null ? '' : 'id="li-'.$this->currentDate.'"').' class="'.(isset($this->dates[$this->currentDate]) ? 'boldd ': (isset($color_class) ? $color_class : '')).'  '.($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).
                ($cellContent==null?'mask':'').'">'.$cellContent.'</td>';
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

}