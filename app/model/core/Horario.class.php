<?php
/**
 * Horario
 *
 * @package    model
 * @subpackage core
 * @author     Marcos David Souza Ramos
 */
class Horario extends TRecord
{
    const TABLENAME = 'horario';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('hora');
    }
}
