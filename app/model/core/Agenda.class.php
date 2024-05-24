<?php
/**
 * Agenda
 *
 * @package    model
 * @subpackage core
 * @author     Thayna Bezerra
 */
class Agenda extends TRecord
{
    const TABLENAME = 'agenda';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}

    private $usuario;
    private $profissional;

    /**
     * Constructor method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('system_user_id'); 
        parent::addAttribute('profissional_id'); 
        parent::addAttribute('date');
        parent::addAttribute('ativa');
        parent::addAttribute('created_at');
        parent::addAttribute('updated_at');
        parent::addAttribute('deleted_at');
    }

    public function set_usuario(SystemUser $object){
        $this-> usuario = $object;
        $this-> system_users_id = $object-> id;
    }

    public function get_usuario(){
        if(empty($this->usuario))
            $this->usuario = new SystemUser($this->system_users_id);

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

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        AgendaItem::where('agenda_id', '=', $this->id)->delete();
    }

    public function getAgendaItem()
    {
        return parent::loadComposite('AgendaItem', 'agenda_id', $this->id);
    }
}
