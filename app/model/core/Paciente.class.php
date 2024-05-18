<?php
/**
 * Paciente
 * @package    model
 * @subpackage core
 * @author     Marcos David Souza Ramos
 */
class Paciente extends TRecord
{
    const TABLENAME = 'paciente';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('nome');
        parent::addAttribute('cpf');
        parent::addAttribute('nascimento');
        parent::addAttribute('celular');
        parent::addAttribute('email');
        parent::addAttribute('obs');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('deleted_at');
    }
}
