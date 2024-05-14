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

    public function set_categoria(Categoria $object)
    {
        $this->categoria = $object;
        $this->categoria_id = $object->id;
    }

    public function get_categoria()
    {
        if (empty($this->categoria))
            $this->categoria = new Categoria($this->categoria_id);

        return $this->categoria;
    }
}
