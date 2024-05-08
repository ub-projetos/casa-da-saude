<?php
/**
 * Postagem
 *
 * @version    7.6
 * @package    model
 * @subpackage admin
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    https://adiantiframework.com.br/license-template
 */
class Postagem extends TRecord
{
    const TABLENAME = 'postagem';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $usuario;
    private $status;
    private $categoria;
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('system_users_id');
        parent::addAttribute('postagem_status_id');
        parent::addAttribute('postagem_categoria_id');
        parent::addAttribute('titulo');
        parent::addAttribute('conteudo');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('deleted_at');
    }

    public function set_usuario(SystemUser $object)
    {
        $this->usuario = $object;
        $this->system_users_id = $object->id;
    }

    public function get_usuario()
    {
        if (empty($this->usuario))
            $this->usuario = new SystemUser($this->system_users_id);
    
        return $this->usuario;
    }


    public function set_status(PostagemStatus $object)
    {
        $this->status = $object;
        $this->postagem_status_id = $object->id;
    }

    public function get_status()
    {
        // loads the associated object
        if (empty($this->status))
            $this->status = new PostagemStatus($this->postagem_status_id);
    
        // returns the associated object
        return $this->status;
    }


    public function set_categoria(PostagemCategoria $object)
    {
        $this->categoria = $object;
        $this->postagem_categoria_id = $object->id;
    }

    public function get_categoria()
    {
        // loads the associated object
        if (empty($this->categoria))
            $this->categoria = new PostagemCategoria($this->postagem_categoria_id);
    
        // returns the associated object
        return $this->categoria;
    }

}


