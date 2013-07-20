<?php
if($_REQUEST['values'])
{
	DBQuery("DELETE FROM PROGRAM_USER_CONFIG WHERE USER_ID='".User('STAFF_ID')."' AND PROGRAM='Gradebook'");
	foreach($_REQUEST['values'] as $title=>$value)
		DBQuery("INSERT INTO PROGRAM_USER_CONFIG (USER_ID,PROGRAM,TITLE,VALUE) values('".User('STAFF_ID')."','Gradebook','$title','".str_replace("\'","''",str_replace('%','',$value))."')");
}

$config_RET = DBGet(DBQuery("SELECT TITLE,VALUE FROM PROGRAM_USER_CONFIG WHERE USER_ID='".User('STAFF_ID')."' AND PROGRAM='Gradebook'"),array(),array('TITLE'));
if(count($config_RET))
{
	foreach($config_RET as $title=>$value)
		$programconfig[$title] = $value[1]['VALUE'];
}

//modif Francois: multiple school periods for a course period
/*$grades = DBGet(DBQuery("SELECT cp.TITLE AS CP_TITLE,c.TITLE AS COURSE_TITLE,cp.COURSE_PERIOD_ID,rcg.TITLE,rcg.ID FROM REPORT_CARD_GRADES rcg,COURSE_PERIODS cp,COURSES c,SCHOOL_PERIODS sp WHERE cp.COURSE_ID=c.COURSE_ID AND cp.PERIOD_ID=sp.PERIOD_ID AND cp.TEACHER_ID='".User('STAFF_ID')."' AND cp.SCHOOL_ID=rcg.SCHOOL_ID AND cp.SYEAR=rcg.SYEAR AND cp.SYEAR='".UserSyear()."' AND rcg.GRADE_SCALE_ID=cp.GRADE_SCALE_ID AND cp.GRADE_SCALE_ID IS NOT NULL AND DOES_BREAKOFF='Y' ORDER BY sp.SORT_ORDER,rcg.BREAK_OFF IS NOT NULL DESC,rcg.BREAK_OFF DESC,rcg.SORT_ORDER DESC"),array(),array('COURSE_PERIOD_ID'));*/
$grades = DBGet(DBQuery("SELECT cp.TITLE AS CP_TITLE,c.TITLE AS COURSE_TITLE,cp.COURSE_PERIOD_ID,rcg.TITLE,rcg.ID FROM REPORT_CARD_GRADES rcg,COURSE_PERIODS cp,COURSES c,SCHOOL_PERIODS sp,COURSE_PERIOD_SCHOOL_PERIODS cpsp WHERE cp.COURSE_PERIOD_ID=cpsp.COURSE_PERIOD_ID AND cp.COURSE_ID=c.COURSE_ID AND cpsp.PERIOD_ID=sp.PERIOD_ID AND cp.TEACHER_ID='".User('STAFF_ID')."' AND cp.SCHOOL_ID=rcg.SCHOOL_ID AND cp.SYEAR=rcg.SYEAR AND cp.SYEAR='".UserSyear()."' AND rcg.GRADE_SCALE_ID=cp.GRADE_SCALE_ID AND cp.GRADE_SCALE_ID IS NOT NULL AND DOES_BREAKOFF='Y' ORDER BY sp.SORT_ORDER,rcg.BREAK_OFF IS NOT NULL DESC,rcg.BREAK_OFF DESC,rcg.SORT_ORDER DESC"),array(),array('COURSE_PERIOD_ID'));

echo '<FORM action="Modules.php?modname='.$_REQUEST['modname'].'" method="POST">';
DrawHeader(_('Gradebook').' - '.ProgramTitle());
DrawHeader('','<INPUT type="submit" value="'._('Save').'" />');
echo '<BR />';
PopTable('header',_('Configuration'));

echo '<fieldset>';
//modif Francois: add translation
//modif Francois: css WPadmin
echo '<legend><b>'._('Assignments').'</b></legend>';
echo '<TABLE>';
if(count($grades))
{
	//if(!$programconfig['ROUNDING'])
	//	$programconfig['ROUNDING'] = 'NORMAL';
//modif Francois: add <label> on radio
	echo '<TR><TD colspan="3"><TABLE><TR><TD colspan="8"><B>'._('Score Rounding').'</B></TD></TR><TR><TD style="text-align:right"><label><INPUT type="radio" name="values[ROUNDING]" value=UP'.(($programconfig['ROUNDING']=='UP')?' checked':'').'>&nbsp;'._('Up').'</label></TD><TD style="text-align:right"><label><INPUT type="radio" name="values[ROUNDING]" value=DOWN'.(($programconfig['ROUNDING']=='DOWN')?' checked':'').'>&nbsp;'._('Down').'</label></TD><TD style="text-align:right"><label><INPUT type="radio" name="values[ROUNDING]" value="NORMAL"'.(($programconfig['ROUNDING']=='NORMAL')?' checked':'').'>&nbsp;'._('Normal').'</label></TD><TD style="text-align:right"><label><INPUT type="radio" name="values[ROUNDING]" value="'.(($programconfig['ROUNDING']=='')?' checked':'').'">&nbsp;'._('None').'</label></TD></TR></TABLE></TD></TR>';
}
if(!$programconfig['ASSIGNMENT_SORTING'])
	$programconfig['ASSIGNMENT_SORTING'] = 'ASSIGNMENT_ID';
echo '<TR><TD colspan="3"><TABLE><TR><TD colspan="6"><B>'._('Assignment Sorting').'</B></TD></TR><TR><TD style="text-align:right"><label><INPUT type="radio" name="values[ASSIGNMENT_SORTING]" value="ASSIGNMENT_ID"'.(($programconfig['ASSIGNMENT_SORTING']=='ASSIGNMENT_ID')?' checked':'').'>&nbsp;'._('Newest First').'</label></TD><TD style="text-align:right"><label><INPUT type="radio" name="values[ASSIGNMENT_SORTING]" value="DUE_DATE"'.(($programconfig['ASSIGNMENT_SORTING']=='DUE_DATE')?' checked':'').'>&nbsp;'._('Due Date').'</label></TD><TD style="text-align:right"><label><INPUT type="radio" name="values[ASSIGNMENT_SORTING]" value=ASSIGNED_DATE'.(($programconfig['ASSIGNMENT_SORTING']=='ASSIGNED_DATE')?' checked':'').'>&nbsp;'._('Assigned Date').'</label></TD></TR></TABLE></TD></TR>';

//modif Francois: add <label> on checkbox
echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><label><INPUT type="checkbox" name="values[WEIGHT]" value="Y"'.(($programconfig['WEIGHT']=='Y')?' checked':'').'>&nbsp;'._('Weight Grades').'</label></TD></TR>';
echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><label><INPUT type="checkbox" name="values[DEFAULT_ASSIGNED]" value="Y"'.(($programconfig['DEFAULT_ASSIGNED']=='Y')?' checked':'').'>&nbsp;'._('Assigned Date defaults to today').'</label></TD></TR>';
echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><label><INPUT type="checkbox" name="values[DEFAULT_DUE]" value="Y"'.(($programconfig['DEFAULT_DUE']=='Y')?' checked':'').'>&nbsp;'._('Due Date defaults to today').'</label></TD></TR>';
echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><label><INPUT type="checkbox" name="values[LETTER_GRADE_ALL]" value="Y"'.(($programconfig['LETTER_GRADE_ALL']=='Y')?' checked':'').'>&nbsp;'._('Hide letter grades for all gradebook assignments').'</label></TD></TR>';
echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><INPUT type="text" name="values[LETTER_GRADE_MIN]" value="'.$programconfig['LETTER_GRADE_MIN'].'" size="3" maxlength="3" />&nbsp;'._('Minimum assignment points for letter grade').'</TD></TR>';
echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><INPUT type="text" name="values[ANOMALOUS_MAX]" value="'.($programconfig['ANOMALOUS_MAX']!=''?$programconfig['ANOMALOUS_MAX']:'100').'" size="3" maxlength="3" />&nbsp;% '._('Allowed maximum percent in Anomalous grades').'</TD></TR>';
echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><INPUT type="text" name="values[LATENCY]" value="'.round($programconfig['LATENCY']).'" size="3" maxlength="3" />&nbsp;'._('Days until ungraded assignment grade appears in Parent/Student gradebook views').'</TD></TR>';
echo '</TABLE>';
echo '</fieldset><BR />';

if ($RosarioModules['Eligibility'])
{
	echo '<fieldset>';
	echo '<legend><b>'._('Eligibility').'</b></legend>';
	echo '<TABLE>';
	echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD><label><INPUT type="checkbox" name="values[ELIGIBILITY_CUMULITIVE]" value="Y"'.(($programconfig['ELIGIBILITY_CUMULITIVE']=='Y')?' checked':'').'>&nbsp;'._('Calculate Eligibility using Cumulative Semester Grades').'</label></TD></TR>';
	echo '</TABLE>';
	echo '</fieldset><BR />';
}

//modif Francois: display grades according to Grades/config.inc.php
if (Config('GRADES_DOES_LETTER_PERCENT')==0) //if letter AND percent grades
{
	echo '<fieldset>';
	echo '<legend><b>'._('Final Grades').'</b></legend>';
	echo '<TABLE>';

	echo '<TR><TD colspan="3"><TABLE><TR><TD colspan="4"><B>'._('Input Format').'</B></TD></TR><TR><TD style="text-align:right"><label><INPUT type="radio" name="values[ONELINE]" value="'.(($programconfig['ONELINE']=='')?' checked':'').'"></TD><TD>'._('Letter').'<BR />'._('Percent').'</label></TD><TD style="text-align:right"><label><INPUT type="radio" name="values[ONELINE]" value="Y"'.(($programconfig['ONELINE']=='Y')?' checked':'').'><span style="white-space:nowrap;"> '._('Letter Percent').'</span></label></TD></TR></TABLE></TD></TR>';

	$comment_codes_RET = DBGet(DBQuery("SELECT rccs.ID,rccs.TITLE,rccc.TITLE AS CODE_TITLE FROM REPORT_CARD_COMMENT_CODE_SCALES rccs,REPORT_CARD_COMMENT_CODES rccc WHERE rccs.SCHOOL_ID='".UserSchool()."' AND rccc.SCALE_ID=rccs.ID ORDER BY rccc.SORT_ORDER,rccs.SORT_ORDER,rccs.ID,rccc.ID"),array(),array('ID'));
	if($comment_codes_RET)
	{
		foreach($comment_codes_RET as $id=>$comments)
		{
		echo '<TR><TD class="valign-top" style="width:30px;"></TD><TD style="text-align:right"><SELECT name="values[COMMENT_'.$id.']><OPTION value="">'._('N/A').'';
		foreach($comments as $key=>$val)
			echo '<OPTION value="'.$val['CODE_TITLE'].'"'.($val['CODE_TITLE']==$programconfig['COMMENT_'.$id]?' selected':'').'>'.$val['CODE_TITLE'];
		echo '</SELECT></TD><TD style="text-align:left;">'.sprintf(_('Default %s comment code'), $comments[1]['TITLE']).'</TD></TR>';
		}
	}
	echo '</TABLE>';
	echo '</fieldset><BR />';
}
/*
foreach($grades as $course_period_id=>$cp_grades)
{
	for($i=1;$i<=count($cp_grades);$i++)
		$grades[$course_period_id][$i] = $grades[$course_period_id][$i]['TITLE'];
}
*/

//$grades = array('A+','A','A-','B+','B','B-','C+','C','C-','D+','D','D-','F');
if(count($grades))
{
	echo '<fieldset>';
	echo '<legend><b>'._('Score Breakoff Points').'</b></legend>';
	echo '<TABLE><TR><TD>';
	foreach($grades as $course_period_id=>$cp_grades)
	{
		$table = '<TABLE>';
		$table .= '<TR><TD rowspan="2" style="text-align:right; width: 100px">'.$cp_grades[1]['COURSE_TITLE'].' - '.mb_substr($cp_grades[1]['CP_TITLE'],0,mb_strrpos(str_replace(' - ',' ^ ',$cp_grades[1]['CP_TITLE']),'^')).'</TD>';
		foreach($cp_grades as $grade)
			$table .= '<TD><B>'.$grade['TITLE'].'</B></TD>';
		$table .= '</TR>';
		$table .= '<TR>';
		foreach($cp_grades as $grade)
			$table .= '<TD><INPUT type="text" name="values['.$course_period_id.'-'.$grade['ID'].']" value="'.$programconfig[$course_period_id.'-'.$grade['ID']].'" size="2" maxlength="5" /></TD>';
		$table .= '</TR>';
		$table .= '</TABLE>';
		echo $table;
		echo '</TD></TR><TR><TD>';
	}
	echo '</TD></TR></TABLE>';
	echo '</fieldset><BR />';
}

$year = DBGet(DBQuery("SELECT TITLE,MARKING_PERIOD_ID,DOES_GRADES,DOES_EXAM FROM SCHOOL_MARKING_PERIODS WHERE MP='FY' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."' ORDER BY SORT_ORDER"));
$semesters = DBGet(DBQuery("SELECT TITLE,MARKING_PERIOD_ID,DOES_GRADES,DOES_EXAM FROM SCHOOL_MARKING_PERIODS WHERE MP='SEM' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."' ORDER BY SORT_ORDER"));
$quarters = DBGet(DBQuery("SELECT TITLE,MARKING_PERIOD_ID,PARENT_ID,DOES_GRADES,DOES_EXAM FROM SCHOOL_MARKING_PERIODS WHERE MP='QTR' AND SYEAR='".UserSyear()."' AND SCHOOL_ID='".UserSchool()."' ORDER BY SORT_ORDER"),array(),array('PARENT_ID'));

echo '<fieldset>';
echo '<legend><b>'._('Final Grading Percentages').'</b></legend>';
echo '<TABLE>';
foreach($semesters as $sem)
	if($sem['DOES_GRADES']=='Y')
	{
		$table = '<TABLE>';
		$table .= '<TR><TD rowspan="2" style="vertical-align:middle;">'.$sem['TITLE'].'&nbsp;</TD>';
		foreach($quarters[$sem['MARKING_PERIOD_ID']] as $qtr)
			$table .= '<TD>'.$qtr['TITLE'].'&nbsp;</TD>';
		if($sem['DOES_EXAM']=='Y')
			$table .= '<TD>'.$sem['TITLE'].' '._('Exam').'</TD>';
		$table .= '</TR><TR>';
		$total = 0;
		foreach($quarters[$sem['MARKING_PERIOD_ID']] as $qtr)
		{
			$table .= '<TD><INPUT type="text" name="values[SEM-'.$qtr['MARKING_PERIOD_ID'].']" value="'.$programconfig['SEM-'.$qtr['MARKING_PERIOD_ID']].'" size="3" maxlength="6" /></TD>';
			$total += $programconfig['SEM-'.$qtr['MARKING_PERIOD_ID']];
		}
		if($sem['DOES_EXAM']=='Y')
		{
			$table .= '<TD><INPUT type="text" name="values[SEM-E'.$sem['MARKING_PERIOD_ID'].']" value="'.$programconfig['SEM-E'.$sem['MARKING_PERIOD_ID']].'" size="3" maxlength="6" /></TD>';
			$total += $programconfig['SEM-E'.$sem['MARKING_PERIOD_ID']];
		}
		if($total!=100)
			$table .= '<TD><span style="color:red">'._('Total').' &#8800; 100%!</span></TD>';
		$table .= '</TR>';
		$table .= '</TABLE>';
		echo '<TR><TD colspan="3">'.$table.'</TD></TR>';
	}

if($year[1]['DOES_GRADES']=='Y')
{
	$table = '<TABLE>';
	$table .= '<TR><TD rowspan="2" style="vertical-align:middle;"><span style="white-space:nowrap;">'.$year[1]['TITLE'].'&nbsp;</span></TD>';
	foreach($semesters as $sem)
	{
		foreach($quarters[$sem['MARKING_PERIOD_ID']] as $qtr)
			$table .= '<TD><span style="white-space:nowrap;">'.$qtr['TITLE'].'&nbsp;</span></TD>';
		if($sem['DOES_GRADES']=='Y')
			$table .= '<TD><span style="white-space:nowrap;">'.$sem['TITLE'].'&nbsp;</span></TD>';
		if($sem['DOES_EXAM']=='Y')
			$table .= '<TD><span style="white-space:nowrap;">'.$sem['TITLE'].'&nbsp;</span> '._('Exam').'</TD>';
	}
	if($year[1]['DOES_EXAM']=='Y')
		$table .= '<TD><span style="white-space:nowrap;">'.$year[1]['TITLE'].'&nbsp;</span> '._('Exam').'</TD>';
	$table .= '</TR><TR>';
	$total = 0;
	foreach($semesters as $sem)
	{
		foreach($quarters[$sem['MARKING_PERIOD_ID']] as $qtr)
		{
			$table .= '<TD><INPUT type="text" name="values[FY-'.$qtr['MARKING_PERIOD_ID'].']" value="'.$programconfig['FY-'.$qtr['MARKING_PERIOD_ID']].'" size="3" maxlength="6" /></TD>';
			$total += $programconfig['FY-'.$qtr['MARKING_PERIOD_ID']];
		}
		if($sem['DOES_GRADES']=='Y')
		{
			$table .= '<TD><INPUT type="text" name="values[FY-'.$sem['MARKING_PERIOD_ID'].']" value="'.$programconfig['FY-'.$sem['MARKING_PERIOD_ID']].'" size="3" maxlength="6" /></TD>';
			$total += $programconfig['FY-'.$sem['MARKING_PERIOD_ID']];
		}
		if($sem['DOES_EXAM']=='Y')
		{
			$table .= '<TD><INPUT type="text" name="values[FY-E'.$sem['MARKING_PERIOD_ID'].']" value="'.$programconfig['FY-E'.$sem['MARKING_PERIOD_ID']].'" size="3" maxlength="6" /></TD>';
			$total += $programconfig['FY-E'.$sem['MARKING_PERIOD_ID']];
		}
	}
	if($year[1]['DOES_EXAM']=='Y')
	{
		$table .= '<TD><INPUT type="text" name="values[FY-E'.$year[1]['MARKING_PERIOD_ID'].']" value="'.$programconfig['FY-E'.$year[1]['MARKING_PERIOD_ID']].'" size="3" maxlength="6" /></TD>';
		$total += $programconfig['FY-E'.$year[1]['MARKING_PERIOD_ID']];
	}
	if($total!=100)
		$table .= '<TD><span style="color:red; white-space:nowrap;">'._('Total').' &#8800; 100%!</span></TD>';
	$table .= '</TR>';
	$table .= '</TABLE>';
	echo '<TR><TD colspan="3">'.$table.'</TD></TR>';
}
echo '</TABLE>';
echo '</fieldset>';

PopTable('footer');
echo '<span class="center"><INPUT type="submit" value="'._('Save').'" /></span>';
echo '</FORM>';
?>