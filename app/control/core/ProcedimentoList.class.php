<?php
/**
 * Procedimento
 *
 * @package    model
 * @subpackage core
 * @author     Thayna Bezerra
 */
class Procedimento extends TRecord
{
    const TABLENAME = 'procedimento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $categoria;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('categoria_id');
        parent::addAttribute('titulo');
        parent::addAttribute('descricao');
        parent::addAttribute('tempo');
        parent::addAttribute('valor');
        parent::addAttribute('ativo');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('deleted_at');
    }

    public function set_usuario(SystemUser $object){
        $this->usuario = $object;
        $this->system_user_id = $object->id;
    }

    public function get_usuario(){
        if (empty($this->usuario))
            $this->usuario = new SystemUser($this->system_user_id);

        return $this->usuario;
    }
    
    public function set_profissional(SystemUser $object)
    {
        $this->profissional = $object;
        $this->profissional_id = $object->id;
    }

    public function get_profissional()
    {
        if (empty($this->profissional))
            $this->profissional = new SystemUser($this->profissional_id);

        return $this->profissional;
    }
}
?>