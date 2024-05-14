<?php
/**
 * Categoria
 *
 * @package    model
 * @subpackage core
 * @author     Thayna Bezerra
 */
class Categoria extends TRecord
{
    const TABLENAME = 'categoria';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $area;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('area_id');
        parent::addAttribute('titulo');
    }
    
    public function set_area(Area $object)
    {
        $this->area = $object;
        $this->area_id = $object->id;
    }

    public function get_area()
    {
        if (empty($this->area))
            $this->area = new Area($this->area_id);

        return $this->area;
    }

}
