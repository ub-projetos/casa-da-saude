<?php
/**
 * PostagemCategoria
 *
 * @version    7.6
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-template
 */
class PostagemCategoria extends TRecord
{
    const TABLENAME = 'postagem_categoria';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('titulo');
        parent::addAttribute('ativo');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('deleted_at');
    }


    public static function list_items()
    {
        return [
            'Y' => 'Sim',
            'N' => 'Não'
        ];
    }
}
