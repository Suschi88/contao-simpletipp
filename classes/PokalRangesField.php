<?php

class PokalRangesField extends Widget {

    protected $strTemplate    = 'be_widget';

    protected $blnSubmitInput = true;

    protected $varValue       = array();


    public function generate() {
        $this->leagueGroups  = Simpletipp::getLeagueGroups($this->activeRecord->leagueID);

        // Initialize the tab index
        if (!\Cache::has('tabindex')) {
            \Cache::set('tabindex', 1);
        }
        $tabindex = \Cache::get('tabindex');

        $return   = '<table id="ctrl_'.$this->strId.'" class="tl_pokalRanges" data-tabindex="'.$tabindex.'"><tbody>';
        $tmpl     = '<tr><td>%s:</td><td><select name="%s_start" class="tl_select" onfocus="Backend.getScrollOffset()">%s</select></td><td><select class="tl_select" onfocus="Backend.getScrollOffset()" name="%s_end">%s</select></td></tr>';

        foreach(SimpletippPokal::$groupAliases as $groupAlias) {


            $start = array_key_exists($groupAlias, $this->varValue) ? $this->varValue[$groupAlias][0] : '';
            $end   = array_key_exists($groupAlias, $this->varValue) ? end($this->varValue[$groupAlias]) : '';
            $return .= sprintf(
                $tmpl, $GLOBALS['TL_LANG']['tl_simpletipp'][$groupAlias][0],
                $groupAlias, $this->getGroupOptions($start),
                $groupAlias, $this->getGroupOptions($end)
            );
        }

        $return .= '</tbody></table>';

        \Cache::set('tabindex', $tabindex);

        return $return;
    }

    private  function getGroupOptions($value) {
        $options = '<option value="-">Bitte wählen...</option>';
        $tmpl    = '<option%s value="%s">%s</option>';
        foreach ($this->leagueGroups as $g) {
                $sel = ($value == $g->title) ? ' selected="selected"': '';
                $options .= sprintf($tmpl, $sel, $g->title, $g->title);
            }
        return $options;
    }

    public function validate() {
        $def = array();
        foreach(SimpletippPokal::$groupAliases as $groupAlias) {
            $def[$groupAlias]['start'] = $this->getPost($groupAlias.'_start');
            $def[$groupAlias]['end']   = $this->getPost($groupAlias.'_end');
        }

        $valueArr     = array();
        $i            = 0;
        $addGroup     = false;
        $currentAlias = SimpletippPokal::$groupAliases[$i];

        foreach (Simpletipp::getLeagueGroups($this->activeRecord->leagueID) as $g) {

            if ($g->title == $def[$currentAlias]['end']) {
                $valueArr[$currentAlias][] = $g->title;
                $currentAlias = SimpletippPokal::$groupAliases[++$i];
                $addGroup     = false;
            }
            if ($g->title == $def[$currentAlias]['start'] || $addGroup) {
                $addGroup = true;
                $valueArr[$currentAlias][] = $g->title;
            }
        }

        \Input::setPost($this->strName, $valueArr);

        $this->varValue = $valueArr;
    }

    protected function validator($varInput) {
        return $varInput;
    }

}


