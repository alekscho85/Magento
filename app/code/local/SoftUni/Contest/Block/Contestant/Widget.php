<?php


class SoftUni_Contest_Block_Contestant_Add
    extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{
    public function getContest() {
        $errorMsg = $this->__('There is no such active contest');
        $selectedContest = $this->getData('contest');

        if (empty($selectedContest)) {
            return $errorMsg;
        }

        $selectedContest = explode('-', $selectedContest);

        $contestArr['id'] = $selectedContest[0];
        $contestArr['title'] = $selectedContest[1];

        return $contestArr;
    }
}