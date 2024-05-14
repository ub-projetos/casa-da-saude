<?php
/**
 * Area
 *
 * @package    model
 * @subpackage core
 * @author     Thayna Bezerra
 */
class Area extends TRecord
{
    const TABLENAME = 'area';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('titulo');
    }
}
