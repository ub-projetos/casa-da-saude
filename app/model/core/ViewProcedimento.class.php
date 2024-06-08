<?php
/**
 * ViewProcedimento
 *
 * @package    model
 * @subpackage core
 * @author     Marcos David Souza Ramos
 */
class ViewProcedimento extends TRecord
{
    const TABLENAME = 'view_procedimento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('area_id');
        parent::addAttribute('titulo_area');
        parent::addAttribute('categoria_id');
        parent::addAttribute('titulo_categoria');
        parent::addAttribute('titulo');
        parent::addAttribute('descricao');
        parent::addAttribute('tempo');
        parent::addAttribute('valor');
        parent::addAttribute('ativo');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('deleted_at');
    }

}
