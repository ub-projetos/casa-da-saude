<?php
/**
 * Atendimento
 *
 * @package    model
 * @subpackage core
 * @author     Thayna Bezerra
 */
class Atendimento extends TRecord
{
    const TABLENAME = 'atendimento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $paciente;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('paciente_id'); 
        parent::addAttribute('data');
        parent::addAttribute('total');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('deleted_at');
    }

    public function set_paciente(SystemUser $object){
        $this-> paciente = $object;
        $this-> system_users_id = $object-> id;
    }

    public function get_paciente(){
        if(empty($this->paciente))
            $this->paciente = new SystemUser($this->paciente_id);

        return $this->paciente;
    }
}
