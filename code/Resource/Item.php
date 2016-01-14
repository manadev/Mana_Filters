<?php
/** 
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Filters_Resource_Item extends Mage_Core_Model_Mysql4_Abstract {
    /**
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @return Varien_Db_Select
     */
    public function selectItems($filter) {
    }

    /**
     * @param Mana_Filters_Model_Filter_Attribute $filter
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Varien_Db_Select
     */
    public function countItems($filter, $collection) {
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $db = $this->_getReadAdapter();
        $attribute = $filter->getAttributeModel();

        $select
            ->joinInner(array('eav' => $this->getTable('catalog/product_index_eav')),
                "`eav`.`entity_id` = `e`.`entity_id` AND
                {$db->quoteInto("`eav`.`attribute_id` = ?", $attribute->getAttributeId())} AND
                {$db->quoteInto("`eav`.`store_id` = ?", $filter->getStoreId())}",
                array(
                    'count' => new Zend_Db_Expr("COUNT(DISTINCT `eav`.`entity_id`)"),
                    'value' => new Zend_Db_Expr("`eav`.`value`")
                )
            )
            ->group(new Zend_Db_Expr("`eav`.`value`"));

        $selectedOptionIds = $filter->getMSelectedValues();
        $isSelectedExpr = count($selectedOptionIds) ? "`eav`.`value` IN (" . implode(', ', $selectedOptionIds). ")" : "1 <> 1";

        $fields = array(
            'sort_order' => new Zend_Db_Expr("`o`.`sort_order`"),
            'value' => new Zend_Db_Expr("`eav`.`value`"),
            'label' => new Zend_Db_Expr("COALESCE(`vs`.`value`, `vg`.`value`)"),
            'm_selected' => new Zend_Db_Expr($isSelectedExpr),
            'm_show_selected' => new Zend_Db_Expr($filter->getFilterOptions()->getIsReverse()
                ? "NOT ($isSelectedExpr)"
                : $isSelectedExpr),
        );
        $itemSelect = $db->select()
            ->from(array('eav' => $select), array('count' => new Zend_Db_Expr("`eav`.`count`")))
            ->joinInner(array('o' => $this->getTable('eav/attribute_option')),
                "`o`.`option_id` = `eav`.`value`", null)
            ->joinInner(array('vg' => $this->getTable('eav/attribute_option_value')),
                $db->quoteInto("`vg`.`option_id` = `eav`.`value` AND `vg`.`store_id` = ?", 0), null)
            ->joinLeft(array('vs' => $this->getTable('eav/attribute_option_value')),
                $db->quoteInto("`vs`.`option_id` = `eav`.`value` AND `vs`.`store_id` = ?", $filter->getStoreId()), null)
            ->columns($fields);

        $sql = $select->__toString();
        $sql = $itemSelect->__toString();
        return $itemSelect;

    }

    /**
     * @param Varien_Db_Select $select
     * @return array
     */
    public function fetch($select) {
        $db = $this->_getReadAdapter();
        return $db->fetchAll($select);
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('catalog');
    }
}