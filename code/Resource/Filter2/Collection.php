<?php
/**
 * @category    Mana
 * @package     Mana_Filters
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* BASED ON SNIPPET: Resources/DB operations with model collections */
/**
 * This resource model handles DB operations with a collection of models of type Mana_Filters_Model_Filter2. All 
 * database specific code for operating collection of Mana_Filters_Model_Filter2 should go here.
 * @author Mana Team
 */
class Mana_Filters_Resource_Filter2_Collection extends Mana_Db_Resource_Object_Collection
{
    /**
     * Invoked during resource collection model creation process, this method associates this 
     * resource collection model with model class and with resource model class
     */
    protected function _construct()
    {
        $this->_init(strtolower('Mana_Filters/Filter2'));
    }

	public function addCodeFilter($codes) {
        $this->addFieldToFilter('code', array('in' => $codes));
        return $this;
    }

    protected function _initSelect()
    {
        $this->getSelect()
            ->from(array('main_table' => $this->getMainTable()))
            ->joinLeft(array('ea' => $this->getTable('eav/attribute')), "`ea`.`attribute_code` = `main_table`.`code` AND `ea`.`attribute_code` <> 'category'", null)
            ->joinLeft(array('et' => $this->getTable('eav/entity_type')),
                "`et`.`entity_type_id` = `ea`.`entity_type_id` AND `et`.`entity_type_code` = 'catalog_product'", null)
            ->joinLeft(array('ca' => $this->getTable('catalog/eav_attribute')), "`ca`.`attribute_id` = `ea`.`attribute_id`", null)
            ->where("`main_table`.`type` = 'category' OR (`et`.`entity_type_id` IS NOT NULL AND `ca`.`is_filterable` <> 0)");
        return $this;
    }
}
