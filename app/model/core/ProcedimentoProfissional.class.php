<?php
/**
 * ProcedimentoProfissional
 * @package    model
 * @subpackage core
 * @author     Marcos David Souza Ramos
 */
class ProcedimentoProfissional extends TRecord
{
    const TABLENAME = 'procedimento_profissional';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('procedimento_id'); 
        parent::addAttribute('profissional_id'); 
    }


}
