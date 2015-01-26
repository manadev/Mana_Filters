<?php
/** 
 * @category    Mana
 * @package     ManaPro_FilterDependent
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Filters_Model_Source_Filter extends Mana_Core_Model_Source_Abstract {
    protected $_currentFilterId;
    protected $_noEmptyValue = false;
    protected $_excluded = array();

    public function setCurrentFilterId($value) {
        $this->_currentFilterId = $value;
        return $this;
    }

    public function setNoEmptyValue($value) {
        $this->_noEmptyValue = $value;

        return $this;
    }

    public function exclude($code) {
        $this->_excluded[$code] = $code;
        return $this;
    }

    protected function _getAllOptions() {
        $result = $this->_noEmptyValue ? array() : array(array('value' => '', 'label' => ''));

        if ($this->adminHelper()->isGlobal()) {
            $collection = $this->getGlobalFilterCollection();

            $select = $collection->getSelect()
                ->reset(Varien_Db_Select::COLUMNS)
                ->columns(array('id', 'name'));

            if ($this->_currentFilterId) {
                $collection->addFieldToFilter('id', array('neq' => $this->_currentFilterId));
            }
        }
        else {
            $collection = $this->getStoreLevelFilterCollection()
                ->addStoreFilter($this->adminHelper()->getStore());
            $select = $collection->getSelect()
                ->reset(Varien_Db_Select::COLUMNS)
                ->columns(array('global_id', 'name'));
			Mage::helper('mana_db')->joinLeft($select,
				'global', Mage::getSingleton('core/resource')->getTableName('mana_filters/filter2'),
				'main_table.global_id = global.id');

            if ($this->_currentFilterId) {
                $collection->addFieldToFilter('global_id', array('neq' => $this->_currentFilterId));
            }
        }

        if (count($this->_excluded)) {
            $collection->addFieldToFilter('code', array('nin' => $this->_excluded));
        }
        $data = $collection->getConnection()->fetchPairs($select);
        foreach ($data as $value => $label) {
            $result[] = array('value' => $value, 'label' => $label);
        }

        return $result;
    }

    #region Dependencies

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Filters_Resource_Filter2_Collection
     */
    public function getGlobalFilterCollection() {
        return Mage::getResourceModel('mana_filters/filter2_collection');
    }

    /**
     * @return Mana_Filters_Resource_Filter2_Store_Collection
     */
    public function getStoreLevelFilterCollection() {
        return Mage::getResourceModel('mana_filters/filter2_store_collection');
    }
    #endregion
}