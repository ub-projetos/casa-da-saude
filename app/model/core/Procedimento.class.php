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

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        // delete the related objects
        SystemUserGroup::where('procedimento_id', '=', $this->id)->delete();
    }


    /**
     * Add a Profissional to the Procedimento
     * @param $object Instance of SystemUser
     */
    public function addProfissional(SystemUser $profissional)
    {
        $object = new ProcedimentoProfissional;
        $object->procedimento_id = $this->id;
        $object->profissional_id = $profissional->id;
        $object->store();
    }

    /**
     * Return the procedure' professionals
     * @return Collection of SystemUser
     */
    public function getProfissionais()
    {
        return parent::loadAggregate('SystemUser', 'ProcedimentoProfissional', 'procedimento_id', 'profissional_id', $this->id);
    }

    /**
     * Get professional ids
     */
    public function getProfissionalIds($as_string = false)
    {
        $ids = array();
        $objects = $this->getProfissionais();

        if ($objects){
            foreach ($objects as $object)
            {
                $ids[] = $object->id;
            }
        }
        
        if ($as_string){
            return implode(',', $ids);
        }
        
        return $ids;
    }
}
